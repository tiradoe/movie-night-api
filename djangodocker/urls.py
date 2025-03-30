"""
URL configuration for djangodocker project.
"""

import knox
from knox import views as knox_views
from django.contrib import admin
from django.urls import path, include
from django.conf.urls.static import static
from django.conf import settings
from rest_framework.routers import DefaultRouter

from users import views as user_views
from rest_framework.authtoken.views import obtain_auth_token

router = DefaultRouter()
router.register(r"api/users", user_views.UserViewSet)
router.register(r"api/groups", user_views.GroupViewSet)

urlpatterns = [
    path("", include(router.urls)),
    path("admin/", admin.site.urls),
    path(r"api/auth/token/", obtain_auth_token),
    path(r"api/auth/login/", user_views.LoginView.as_view(), name="knox_login"),
    path(r"api/auth/register/", user_views.register, name="register"),
    path(r"api/auth/", include("knox.urls")),
] + static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
