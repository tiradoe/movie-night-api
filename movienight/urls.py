"""
URL configuration for movienight project.
"""

import knox
from knox import views as knox_views
from django.contrib import admin
from django.urls import path, include
from django.conf.urls.static import static
from django.conf import settings
from rest_framework.routers import DefaultRouter

from users import views as user_views
from movie_manager import views as movie_views
from movie_db import views as movie_db_views
from rest_framework.authtoken.views import obtain_auth_token

router = DefaultRouter()
router.register(r"v1/users", user_views.UserViewSet)
router.register(r"v1/groups", user_views.GroupViewSet)
router.register(r"v1/movies", movie_views.MovieViewset)
router.register(r"v1/lists", movie_views.MovieListViewset)
router.register(r"v1/schedules", movie_views.ScheduleViewset)
router.register(r"v1/showings", movie_views.ShowingViewset)

urlpatterns = [
    path("", include(router.urls)),
    path("admin/", admin.site.urls),
    path(r"v1/auth/token/", obtain_auth_token),
    path(r"v1/auth/login/", user_views.LoginView.as_view(), name="knox_login"),
    path(r"v1/auth/register/", user_views.register, name="register"),
    path(r"v1/movies/search", movie_db_views.omdb_search, name="omdb_search"),
    path(r"v1/auth/", include("knox.urls")),
] + static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
