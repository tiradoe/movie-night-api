from django.contrib.auth.models import User
from django.db import models
from django.db.models import SET_NULL


class Movie(models.Model):
    title = models.CharField(max_length=100)
    imdb_id = models.CharField(max_length=100, db_index=True, unique=True)
    year = models.IntegerField(null=True, blank=True)
    director = models.CharField(max_length=500, null=True, blank=True)
    actors = models.TextField(null=True, blank=True)
    plot = models.TextField(null=True, blank=True)
    genre = models.CharField(max_length=100, null=True, blank=True)
    mpaa_rating = models.CharField(max_length=20, null=True, blank=True)
    critic_scores = models.TextField(null=True, blank=True)
    poster = models.TextField(null=True, blank=True)
    added_by = models.ForeignKey(User, on_delete=SET_NULL, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ["title"]

    def __str__(self):
        return self.title


class MovieList(models.Model):
    name = models.CharField(max_length=100, db_index=True)
    public = models.BooleanField(default=False, db_index=True)
    slug = models.SlugField(max_length=100, default="", db_index=True)
    owner = models.ForeignKey(User, on_delete=models.CASCADE)
    movies = models.ManyToManyField(Movie)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ["name"]
        indexes = [
            models.Index(fields=["public", "owner"]),
        ]

    def __str__(self):
        return self.name


class Schedule(models.Model):
    name = models.CharField(max_length=100)
    owner = models.ForeignKey(User, on_delete=models.CASCADE)
    public = models.BooleanField(default=False)
    slug = models.SlugField(max_length=100, default="", db_index=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ["name"]

    def __str__(self):
        return self.name


class Showing(models.Model):
    movie = models.ForeignKey(Movie, on_delete=models.CASCADE)
    owner = models.ForeignKey(User, on_delete=models.CASCADE)
    schedule = models.ForeignKey(Schedule, on_delete=models.CASCADE)
    public = models.BooleanField(default=False)
    showtime = models.DateTimeField()
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ["showtime"]

    def __str__(self):
        showtime = self.showtime.strftime("%Y-%m-%d %H:%M")
        return showtime
