from django.http import JsonResponse
from django.db import models
from django.contrib.auth.models import User
from rest_framework import permissions, viewsets
from rest_framework.decorators import action
from rest_framework.exceptions import NotFound

from movie_db.db_providers.omdb import OMDb
from movie_manager.models import MovieList, Movie

from knox.auth import TokenAuthentication

from movie_manager.permissions import ReadOnly
from movie_manager.serializers import MovieListSerializer, MovieListListSerializer


class MovieListViewset(viewsets.ModelViewSet):
    queryset = MovieList.objects.all()
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated | ReadOnly]

    def get_serializer_class(self):
        if self.action == "list":
            return MovieListListSerializer
        else:
            return MovieListSerializer

    def get_queryset(self):
        base_qs = MovieList.objects.all()

        if self.action == "list":
            if self.request.user.is_authenticated:
                return base_qs.filter(
                    models.Q(public=True) | models.Q(owner=self.request.user)
                ).order_by("name")

            return base_qs.filter(public=True).order_by("name")
        else:
            return MovieList.objects.prefetch_related(
                "movies", "movies__showing_set"
            ).order_by("name")

    def perform_create(self, serializer):
        serializer.save(owner=self.request.user)

    def get_permissions(self):
        if self.action in ["update", "partial_update", "destroy"]:
            self.permission_classes = [permissions.IsAuthenticated]
        return super().get_permissions()

    def create(self, request, *args, **kwargs):
        movie_list = MovieList.objects.create(
            name=request.data.get("name"),
            owner=request.user,
        )

        return JsonResponse(MovieListSerializer(movie_list).data)

    def update(self, request, pk=None, *args, **kwargs):
        movie_list = MovieList.objects.get(pk=pk)
        movie_list.name = request.data.get("name")
        movie_list.owner = User.objects.get(pk=request.data.get("owner"))

        if request.data.get("movies"):
            movie_ids = request.data.get("movies")
            for movie_id in movie_ids:
                try:
                    movie = Movie.objects.get(pk=movie_id)
                    movie_list.movies.add(movie)
                except Movie.DoesNotExist:
                    raise NotFound(f"Movie {movie_id} does not exist")

            removed_movies = Movie.objects.exclude(id__in=movie_ids)
            for removed_movie in removed_movies:
                removed_movie.delete()

        movie_list.save()

        return JsonResponse(MovieListSerializer(movie_list).data)

    @action(
        detail=True, methods=["put", "delete"], url_path="movie/(?P<imdb_id>tt[0-9]+)"
    )
    def add_movie(self, request, pk=None, imdb_id=None, *args, **kwargs):
        if request.method == "DELETE":
            return self.remove_movie(request, pk, imdb_id)

        movie_list = MovieList.objects.get(pk=pk)
        try:
            new_movie = Movie.objects.get(imdb_id=imdb_id)
        except Movie.DoesNotExist:
            omdb = OMDb()
            movie = omdb.search(imdb_id, {"type": "imdb_id"})

            new_movie = Movie.objects.create(
                title=movie["title"],
                actors=movie["actors"],
                year=movie["year"],
                imdb_id=movie["imdb_id"],
                poster=movie["poster"],
                plot=movie["plot"],
                genre=movie["genre"],
                critic_scores=movie["critic_scores"],
                mpaa_rating=movie["mpaa_rating"],
                director=movie["director"],
                added_by_id=request.user.id,
            )

        movie_list.movies.add(new_movie)

        return JsonResponse(MovieListSerializer(movie_list).data)

    @staticmethod
    def remove_movie(request, pk=None, imdb_id=None, *args, **kwargs):
        movie = Movie.objects.filter(imdb_id=imdb_id).first()

        movie_list = MovieList.objects.get(pk=pk)
        movie_list.movies.remove(movie)

        return JsonResponse(MovieListSerializer(movie_list).data)
