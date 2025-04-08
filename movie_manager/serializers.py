from gunicorn.config import User
from rest_framework import serializers
from movie_manager.models import Movie, MovieList, Schedule, Showing


class MovieSerializer(serializers.ModelSerializer):
    class Meta:
        model = Movie
        fields = '__all__'


class MovieListSerializer(serializers.ModelSerializer):
    movie_count = serializers.SerializerMethodField()
    movies = MovieSerializer(read_only=True, many=True)

    class Meta:
        model = MovieList
        fields = ["id","name","owner","public", "movies", "movie_count"]


    def get_movie_count(self, obj):
        return len(obj.movies.all())

class UserSerializer(serializers.Serializer):
    class Meta:
        model = User
        fields = ["id", "username"]


class ShowingSerializer(serializers.ModelSerializer):
    movie = MovieSerializer(read_only=True)

    class Meta:
        model = Showing
        fields = ["public", "showtime", "movie", "owner"]


class ScheduleSerializer(serializers.ModelSerializer):
    name = serializers.CharField(read_only=True)
    showings = ShowingSerializer(read_only=True, many=True)

    class Meta:
        model = Schedule
        fields = ["name", "owner","public","slug", "showings"]

