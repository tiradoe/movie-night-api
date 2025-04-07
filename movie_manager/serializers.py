from itertools import count

from rest_framework import serializers
from movie_manager.models import Movie, MovieList

class MovieSerializer(serializers.ModelSerializer):
    class Meta:
        model = Movie
        fields = '__all__'


class MovieListSerializer(serializers.ModelSerializer):
    movie_count = serializers.SerializerMethodField()

    class Meta:
        model = MovieList
        fields = ["id","name","owner","public", "movie_count"]


    def get_movie_count(self, obj):
        return len(obj.movies.all())
