from django.http import HttpResponse, JsonResponse
from django.contrib.auth.models import User
from rest_framework import permissions, viewsets
from knox.auth import TokenAuthentication
from rest_framework.exceptions import NotFound

from movie_manager.models import Movie, MovieList
from movie_manager.serializers import MovieListSerializer, MovieSerializer


# Create your views here.
class MovieViewset(viewsets.ModelViewSet):
    fields = '__all__'
    queryset = Movie.objects.all().order_by("title")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = MovieSerializer

class MovieListViewset(viewsets.ModelViewSet):
    fields = '__all__'
    queryset = MovieList.objects.all().order_by("name")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = MovieListSerializer

    def update(self, request, pk=None, *args, **kwargs):
        movie_list = MovieList.objects.get(pk=pk)
        movie_list.name = request.data.get('name')
        movie_list.owner = User.objects.get(pk=request.data.get("owner"))

        if request.data.get('movies'):
            movie_ids = request.data.get('movies')
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
