from zoneinfo import ZoneInfo

from django.contrib import admin
from django.utils import timezone

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
    list_display = ["local_showtime", "movie"]

    def local_showtime(self, obj):
        if obj.showtime:
            target_tz = ZoneInfo("America/Chicago")
            with timezone.override(target_tz):
                local_time = timezone.localtime(obj.showtime)
                return local_time.strftime("%Y-%m-%d %H:%M")
        return "Invalid datetime"

    local_showtime.short_description = "Showtime (Local)"
    local_showtime.admin_order_field = "showtime"
