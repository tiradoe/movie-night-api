from django.contrib import admin

from movie_manager.models import Movie, MovieList, Schedule, Showing


# Register your models here.
@admin.register(Movie)
class MovieAdmin(admin.ModelAdmin):
    list_display = ["title", "imdb_id", "added_by"]


@admin.register(MovieList)
class MovieListAdmin(admin.ModelAdmin):
    list_display = ["name", "owner"]


@admin.register(Schedule)
class ScheduleAdmin(admin.ModelAdmin):
    list_display = ["name", "owner"]


@admin.register(Showing)
class ShowingAdmin(admin.ModelAdmin):
    list_display = ["showtime", "movie"]
