import os

from movie_db.movie_db import MovieDB
import requests

from movie_db.serializers import MovieSerializer, MovieResultSerializer


class OMDb(MovieDB):
    def __init__(self):
        api_key = os.getenv("OMDB_API_KEY")
        self.api_key = f"{api_key}"
        self.base_url = "https://www.omdbapi.com/?apikey=" + self.api_key
        super().__init__()

    def search(self, query, options=None):
        if options["type"] == "imdb_id":
            return self.search_by_imdb_id(query)
        elif options["type"] == "title":
            return self.search_by_title(query)
        else:
            return self.search_by_term(query)

    def search_by_title(self, title):
        response = requests.get(self.base_url + "&t=" + title).json()
        return MovieSerializer(response).data

    def search_by_imdb_id(self, imdb_id):
        response = requests.get(self.base_url + "&i=" + imdb_id).json()
        return MovieSerializer(response).data

    def search_by_term(self, term):
        response = requests.get(self.base_url + "&s=" + term).json()
        try:
            return MovieResultSerializer(response["Search"], many=True).data
        except KeyError:
            return {"error": response}
