import datetime

from django.http import JsonResponse
from knox.auth import TokenAuthentication
from rest_framework import viewsets, permissions

from movie_manager.models import Schedule, Showing
from movie_manager.permissions import ReadOnly
from movie_manager.serializers import (
    ScheduleSerializer,
    ShowingSerializer,
    MovieSerializer,
)


class ScheduleViewset(viewsets.ModelViewSet):
    queryset = Schedule.objects.all().order_by("name")
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated | ReadOnly]

    serializer_class = ScheduleSerializer

    def retrieve(self, request, pk=None, *args, **kwargs):
        # Get the schedule instance
        instance = self.get_object()
        now = datetime.datetime.now()
        # get time from start of day
        today = datetime.datetime(now.year, now.month, now.day)

        upcoming_showings = Showing.objects.filter(
            showtime__gte=today, schedule=instance
        )

        # Create a serialized response
        serializer = self.get_serializer(instance)
        data = serializer.data

        # Replace all showings with only future showings
        data["showings"] = ShowingSerializer(upcoming_showings, many=True).data

        if request.GET.get("past_showings") == "true":
            past_showings = Showing.objects.filter(
                showtime__lt=today, schedule=instance
            )

            # Add both to the response
            data["past_showings"] = [
                {
                    "id": showing.id,
                    "showtime": showing.showtime.isoformat(),
                    "movie": MovieSerializer(showing.movie).data,
                }
                for showing in past_showings
            ]
        else:
            data["past_showings"] = []

        return JsonResponse(data)
