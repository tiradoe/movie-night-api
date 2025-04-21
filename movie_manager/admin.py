from django.contrib import admin

from movie_manager.models import Movie, MovieList, Schedule, Showing


# Register your models here.
@admin.register(Movie)
class MovieAdmin(admin.ModelAdmin):
    pass


@admin.register(MovieList)
class MovieListAdmin(admin.ModelAdmin):
    pass


@admin.register(Schedule)
class ScheduleAdmin(admin.ModelAdmin):
    pass


@admin.register(Showing)
class ShowingAdmin(admin.ModelAdmin):
    pass
