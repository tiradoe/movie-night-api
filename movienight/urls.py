"""
URL configuration for movienight project.
"""

from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.urls import path, include
from rest_framework.authtoken.views import obtain_auth_token
from rest_framework.routers import DefaultRouter

from movie_db import views as movie_db_views
from movie_manager.viewsets import (
    MovieViewset,
    MovieListViewset,
    ScheduleViewset,
    ShowingViewset,
)
from users import views as user_views
from users.viewsets import UserViewSet, GroupViewSet
from users.viewsets.user import register, UserProfileViewSet

router = DefaultRouter()
router.register(r"v1/users", UserViewSet)
router.register(r"v1/groups", GroupViewSet)
router.register(r"v1/movies", MovieViewset)
router.register(r"v1/lists", MovieListViewset)
router.register(r"v1/schedules", ScheduleViewset)
router.register(r"v1/showings", ShowingViewset)
router.register(r"v1/users/profiles/", UserProfileViewSet)

urlpatterns = [
                  path("", include(router.urls)),
                  path("admin/", admin.site.urls),
                  path(r"v1/auth/token/", obtain_auth_token),
                  path(r"v1/auth/login/", user_views.LoginView.as_view(), name="knox_login"),
                  path(r"v1/auth/register/", register, name="register"),
                  path(r"v1/movies/search", movie_db_views.omdb_search, name="omdb_search"),
                  path(r"v1/auth/", include("knox.urls")),
                  path('v1/users/profile', UserProfileViewSet.as_view({"get": "current_user_profile"}),
                       name="current_user_profile"),
                  path('v1/users/profiles/<str:user__username>/', UserProfileViewSet.as_view({"get": "retrieve"}))
              ] + static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
