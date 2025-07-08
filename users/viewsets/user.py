from django.contrib.auth.models import User
from django.http import JsonResponse
from knox.auth import TokenAuthentication
from rest_framework import viewsets, permissions, status
from rest_framework.decorators import api_view, action
from rest_framework.response import Response

from users.models import UserProfile
from users.permissions import ReadOnly
from users.serializers import UserSerializer, UserProfileSerializer


class UserViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    queryset = User.objects.all().order_by("-date_joined")
    serializer_class = UserSerializer


class UserProfileViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated | ReadOnly]

    queryset = UserProfile.objects.all()
    serializer_class = UserProfileSerializer
    lookup_field = "user__username"

    @action(detail=False)
    def current_user_profile(self, request, *args, **kwargs):
        try:
            user = request.user
        except User.DoesNotExist:
            return Response([], status=status.HTTP_404_NOT_FOUND)

        try:
            user_profile = UserProfile.objects.get(user=user)
        except UserProfile.DoesNotExist:
            user_profile = UserProfile(
                user=user,
            )

            user_profile.save()

        return JsonResponse(UserProfileSerializer(user_profile).data)

    def retrieve(self, request, pk=None, *args, **kwargs):
        try:
            username = kwargs.get('user__username')
            user = User.objects.get(username=username)
        except User.DoesNotExist:
            return Response([], status=status.HTTP_404_NOT_FOUND)

        try:
            user_profile = UserProfile.objects.get(user=user)
        except UserProfile.DoesNotExist:
            user_profile = UserProfile(
                user=user,
            )

            user_profile.save()

        return JsonResponse(UserProfileSerializer(user_profile).data)


@api_view(["POST"])
def register(request):
    user_data = UserSerializer(data=request.data)

    if user_data.is_valid():
        User.objects.create_user(**user_data.validated_data)
        return Response(request.data, status=status.HTTP_201_CREATED)
    else:
        return Response([], status=status.HTTP_400_BAD_REQUEST)
