from rest_framework import serializers


class MovieSerializer(serializers.Serializer):
    director = serializers.CharField(source="Director")
    genre = serializers.CharField(source="Genre")
    imdb_id = serializers.CharField(source="imdbID")
    critic_scores = serializers.CharField(source="Ratings")
    mpaa_rating = serializers.CharField(source="Rated")
    media_type = serializers.CharField(source="Type")
    plot = serializers.CharField(source="Plot")
    poster = serializers.CharField(source="Poster")
    runtime = serializers.CharField(source="Runtime")
    title = serializers.CharField(source="Title")
    year = serializers.CharField(source="Year")


class MovieResultSerializer(serializers.Serializer):
    title = serializers.CharField(source="Title")
    year = serializers.CharField(source="Year")
    imdb_id = serializers.CharField(source="imdbID")
    media_type = serializers.CharField(source="Type")
    poster = serializers.CharField(source="Poster")
