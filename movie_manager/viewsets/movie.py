from django.http import JsonResponse
from rest_framework import permissions, viewsets

from movie_db.db_providers.omdb import OMDb
from movie_manager.models import Movie

from knox.auth import TokenAuthentication

from movie_manager.serializers import MovieSerializer


class MovieViewset(viewsets.ModelViewSet):
    queryset = Movie.objects.all().order_by("title")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    serializer_class = MovieSerializer

    def update(self, request, pk=None, *args, **kwargs):
        omdb = OMDb()
        updated_movie = omdb.search(request.data.get("imdb_id"), {"type": "imdb_id"})

        movie = Movie.objects.get(pk=pk)

        movie.title = updated_movie["title"]
        movie.actors = updated_movie["actors"]
        movie.year = updated_movie["year"]
        movie.critic_scores = updated_movie["critic_scores"]
        movie.mpaa_rating = updated_movie["mpaa_rating"]
        movie.director = updated_movie["director"]
        movie.poster = updated_movie["poster"]
        movie.plot = updated_movie["plot"]
        movie.genre = updated_movie["genre"]

        movie.save()

        return JsonResponse(MovieSerializer(movie).data)
