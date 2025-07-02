from django.http import JsonResponse
from django.utils.dateparse import parse_datetime
from knox.auth import TokenAuthentication
from rest_framework import viewsets, permissions

from movie_manager.models import Showing, Movie, Schedule
from movie_manager.permissions import ReadOnly
from movie_manager.serializers import ShowingSerializer


class ShowingViewset(viewsets.ModelViewSet):
    queryset = Showing.objects.all().order_by("showtime")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated | ReadOnly]

    serializer_class = ShowingSerializer

    def create(self, request, *args, **kwargs):
        movie_id = request.data.get("movie")
        movie = Movie.objects.get(pk=movie_id)

        schedule_id = request.data.get("schedule")
        schedule = Schedule.objects.get(pk=schedule_id)

        showtime_str = request.data.get("showtime")
        showtime = parse_datetime(showtime_str)

        showing = Showing.objects.create(
            movie=movie,
            schedule=schedule,
            showtime=showtime,
            public=request.data.get("public"),
            owner=request.user,
        )

        return JsonResponse(ShowingSerializer(showing).data)
