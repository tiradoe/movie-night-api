from django.db import models
from django.contrib.auth.models import User

# Create your models here.
class Movie(models.Model):
    class Meta:
        ordering = ["title"]

    title = models.CharField(max_length=100)
    imdb_id = models.CharField(max_length=100)
    year = models.IntegerField()
    critic_score = models.CharField(max_length=500)
    genre = models.CharField(max_length=100)
    director = models.CharField(max_length=500)
    actors = models.CharField(max_length=500)
    plot = models.CharField(max_length=500)
    poster = models.CharField(max_length=500)
    last_watched = models.DateTimeField()
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    def __str__(self):
        return self.title


class MovieList(models.Model):
    class Meta:
        ordering = ["name"]

    name = models.CharField(max_length=100)
    public = models.BooleanField(default=False)
    owner = models.ForeignKey(User, on_delete=models.CASCADE)
    movies = models.ManyToManyField(Movie)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    def __str__(self):
        return self.name
