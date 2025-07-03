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
        return Showing.objects.filter(movie_id=obj.id).exists()


class MovieListListSerializer(serializers.ModelSerializer):
    movie_count = serializers.SerializerMethodField()

    class Meta:
        model = MovieList
        fields = ["id", "name", "owner", "public", "movie_count"]

    def get_movie_count(self, obj):
        return obj.movies.count()


class MovieListSerializer(serializers.ModelSerializer):
    movies = MovieSerializer(read_only=True, many=True)
    serializer_class = MovieSerializer
    owner = serializers.PrimaryKeyRelatedField(read_only=True)

    def get_queryset(self):
        return MovieList.objects.prefetch_related("movies", "movies__showing_set")

    class Meta:
        model = MovieList
        fields = ["id", "name", "owner", "public", "movies"]


class UserSerializer(serializers.Serializer):
    class Meta:
        model = User
        fields = ["id", "username"]


class ShowingSerializer(serializers.ModelSerializer):
    movie = MovieSerializer(read_only=True)

    class Meta:
        model = Showing
        fields = ["id", "public", "showtime", "movie", "owner"]

    # def to_internal_value(self, data):
    #    validated_data = super().to_internal_value(data)

    #    if "showtime" in validated_data and timezone.is_naive(
    #        validated_data["showtime"]
    #    ):
    #        validated_data["showtime"] = timezone.make_aware(validated_data["showtime"])

    #    return validated_data


class ScheduleSerializer(serializers.ModelSerializer):
    showings = ShowingSerializer(source="showing_set", read_only=True, many=True)

    class Meta:
        model = Schedule
        fields = ["name", "owner", "public", "slug", "showings"]
