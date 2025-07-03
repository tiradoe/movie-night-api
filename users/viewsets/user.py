from django.contrib.auth.models import User, Group
from knox.auth import TokenAuthentication
from rest_framework import viewsets, permissions, status
from rest_framework.decorators import api_view
from rest_framework.response import Response

from users.serializers import UserSerializer, GroupSerializer


class UserViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    queryset = User.objects.all().order_by("-date_joined")
    serializer_class = UserSerializer


@api_view(["POST"])
def register(request):
    user_data = UserSerializer(data=request.data)

    if user_data.is_valid():
        User.objects.create_user(**user_data.validated_data)
        return Response(request.data, status=status.HTTP_201_CREATED)
    else:
        return Response([], status=status.HTTP_400_BAD_REQUEST)
