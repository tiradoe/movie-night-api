import datetime
import json

from django.http import JsonResponse
from django.contrib.auth.models import User
from django.utils.dateparse import parse_datetime
from rest_framework import permissions, viewsets
from knox.auth import TokenAuthentication
from rest_framework.decorators import action, api_view
from rest_framework.exceptions import NotFound
from rest_framework.permissions import AllowAny, SAFE_METHODS

from movie_db.db_providers.omdb import OMDb
from movie_manager.models import Movie, MovieList, Schedule, Showing
from movie_manager.serializers import (
    MovieListSerializer,
    MovieSerializer,
    ScheduleSerializer,
    ShowingSerializer,
)


class ReadOnly(permissions.BasePermission):
    def has_permission(self, request, view):
        return request.method in SAFE_METHODS


# Create your views here.
class MovieViewset(viewsets.ModelViewSet):
    queryset = Movie.objects.all().order_by("title")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = MovieSerializer


class MovieListViewset(viewsets.ModelViewSet):
    queryset = MovieList.objects.all().order_by("name")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated | ReadOnly]

    serializer_class = MovieListSerializer

    def create(self, request, *args, **kwargs):
        movie_list = MovieList.objects.create(
            name=request.data.get("name"),
            owner=request.user,
        )

        return JsonResponse(MovieListSerializer(movie_list).data)

    def retrieve(self, request, pk=None, *args, **kwargs):
        movie_list = MovieList.objects.get(pk=pk)
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

    def remove_movie(self, request, pk=None, imdb_id=None, *args, **kwargs):
        movie = Movie.objects.filter(imdb_id=imdb_id).first()

        movie_list = MovieList.objects.get(pk=pk)
        movie_list.movies.remove(movie)

        return JsonResponse(MovieListSerializer(movie_list).data)


class ScheduleViewset(viewsets.ModelViewSet):
    queryset = Schedule.objects.all().order_by("name")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = ScheduleSerializer

    def retrieve(self, request, pk=None, *args, **kwargs):
        # Get the schedule instance
        instance = self.get_object()
        today = datetime.datetime.now()

        upcoming_showings = instance.showings.filter(showtime__gte=today)

        # Create a serialized response
        serializer = self.get_serializer(instance)
        data = serializer.data

        # Replace all showings with only future showings
        data["showings"] = ShowingSerializer(upcoming_showings, many=True).data

        if request.GET.get("past_showings") == "true":
            past_showings = instance.showings.filter(showtime__lt=today)

            # Add both to the response
            data["past_showings"] = [
                {
                    "id": showing.id,
                    "showtime": showing.showtime.isoformat(),
                    "movie": MovieSerializer(showing.movie).data,
                }
                for showing in past_showings
            ]
        else:
            data["past_showings"] = []

        return JsonResponse(data)


class ShowingViewset(viewsets.ModelViewSet):
    queryset = Showing.objects.all().order_by("showtime")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = ShowingSerializer

    def create(self, request, *args, **kwargs):
        movie_id = request.data.get("movie")
        movie = Movie.objects.get(pk=movie_id)

        schedule_id = request.data.get("schedule")
        schedule = Schedule.objects.get(pk=schedule_id)

        showtime_str = request.data.get("showtime")
        showtime = parse_datetime(showtime_str)

        showing = Showing.objects.create(
            movie=movie,
            schedule=schedule,
            showtime=showtime,
            public=request.data.get("public"),
            owner=request.user,
        )

        schedule.showings.add(showing)

        return JsonResponse(ShowingSerializer(showing).data)
