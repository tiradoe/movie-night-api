import json

from django.contrib.auth.models import User
from django.utils import timezone
from freezegun import freeze_time
from rest_framework import status
from rest_framework.test import APITestCase, APIClient

from movie_manager.models import Movie, Schedule, Showing


class ShowingViewsetTestCase(APITestCase):
    def setUp(self):
        self.client: APIClient = APIClient()
        self.movie: Movie = Movie.objects.create(title="Test Movie")
        self.owner: User = User.objects.create(id=1, username="test_user")
        self.schedule: Schedule = Schedule.objects.create(
            owner=self.owner, name="Test Schedule"
        )

    def test_it_creates_a_new_showing(self):
        self.client.force_authenticate(user=self.owner)

        showing_time = timezone.now().isoformat().replace("+00:00", "Z")
        response = self.client.post(
            "/v1/showings/",
            {
                "movie": self.movie.id,
                "public": True,
                "schedule": self.schedule.id,
                "showtime": showing_time,
            },
        )

        response_data = json.loads(response.content)
        self.assertEqual(response.status_code, status.HTTP_201_CREATED)
        self.assertEqual(response_data.get("showtime"), showing_time)
        self.assertEqual(response_data.get("movie").get("title"), "Test Movie")

    @freeze_time("2025-07-02 23:59:00", tz_offset=-5)
    def test_it_returns_active_showings(self):
        self.client.force_authenticate(user=self.schedule.owner)

        showtimes_america_chicago_utc = [
            "2025-07-03T04:00:59.000Z",  # 7/2/25 11:59pm
            "2025-07-01T04:00:59.000Z",  # 6/30/25 11:59pm
            "2025-07-08T04:00:59.000Z",  # 7/7/25 11:59pm
        ]

        for showtime in showtimes_america_chicago_utc:
            Showing.objects.create(
                movie=self.movie,
                schedule=self.schedule,
                showtime=showtime,
                public=True,
                owner=self.schedule.owner,
            )

        response = self.client.get(f"/v1/schedules/{self.schedule.id}/")
        parsed_schedule = json.loads(response.content)

        self.assertEqual(response.status_code, status.HTTP_200_OK)
        self.assertEqual(len(parsed_schedule.get("showings")), 2)


class ScheduleViewsetTestCase(APITestCase):
    def setUp(self):
        self.client: APIClient = APIClient()
        self.test_user: User = User.objects.create(id=1, username="test_user")

    def test_it_creates_a_new_schedule(self):
        self.client.force_authenticate(user=self.test_user)
        response = self.client.post(
            "/v1/schedules/",
            {
                "name": "Test Schedule",
                "owner": self.test_user.id,
                "public": True,
                "slug": "test-schedule",
            },
        )

        response_data = json.loads(response.content)

        self.assertEqual(response.status_code, status.HTTP_201_CREATED)
        self.assertEqual(response_data.get("name"), "Test Schedule")
        self.assertEqual(response_data.get("owner"), 1)
        self.assertEqual(response_data.get("public"), True)
        self.assertEqual(response_data.get("slug"), "test-schedule")
