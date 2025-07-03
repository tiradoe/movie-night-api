from django.http import JsonResponse
from django.utils import timezone
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
        now = timezone.now()

        upcoming_showings = Showing.objects.filter(showtime__gte=now, schedule=instance)

        serializer = self.get_serializer(instance)
        data = serializer.data

        # Replace all showings with only future showings
        data["showings"] = ShowingSerializer(upcoming_showings, many=True).data

        if request.GET.get("past_showings") == "true":
            past_showings = Showing.objects.filter(showtime__lt=now, schedule=instance)

            # Add both to the response
            data["past_showings"] = [
                {
                    "id": past_showing.id,
                    "showtime": past_showing.showtime.isoformat(),
                    "movie": MovieSerializer(past_showing.movie).data,
                }
                for past_showing in past_showings
            ]
        else:
            data["past_showings"] = []

        return JsonResponse(data)
