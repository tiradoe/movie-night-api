from django.utils import timezone
from gunicorn.config import User
from rest_framework import serializers
from movie_manager.models import Movie, MovieList, Schedule, Showing


class MovieSerializer(serializers.ModelSerializer):
    has_been_scheduled = serializers.SerializerMethodField()

    class Meta:
        model = Movie
        fields = [
            "id",
            "title",
            "imdb_id",
            "year",
            "director",
            "actors",
            "plot",
            "genre",
            "mpaa_rating",
            "critic_scores",
            "poster",
            "added_by_id",
            "has_been_scheduled",
        ]

    def get_has_been_scheduled(self, obj):
        return len(Showing.objects.filter(movie_id=obj.id).all()) > 0


class MovieListSerializer(serializers.ModelSerializer):
    movie_count = serializers.SerializerMethodField()
    movies = MovieSerializer(read_only=True, many=True)

    class Meta:
        model = MovieList
        fields = ["id", "name", "owner", "public", "movies", "movie_count"]

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
        fields = ["id", "public", "showtime", "movie", "owner"]

    def to_internal_value(self, data):
        validated_data = super().to_internal_value(data)

        if "showtime" in validated_data and timezone.is_naive(
            validated_data["showtime"]
        ):
            validated_data["showtime"] = timezone.make_aware(validated_data["showtime"])

        return validated_data


class ScheduleSerializer(serializers.ModelSerializer):
    name = serializers.CharField(read_only=True)
    showings = ShowingSerializer(source="showing_set", read_only=True, many=True)

    class Meta:
        model = Schedule
        fields = ["name", "owner", "public", "slug", "showings"]
