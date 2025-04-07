from django.contrib.auth import login
from django.contrib.auth.models import Group, User, AnonymousUser
from rest_framework import permissions, viewsets, status
from knox.auth import TokenAuthentication
from knox.views import LoginView as KnoxLoginView
from rest_framework.authtoken.serializers import AuthTokenSerializer
from rest_framework.response import Response
from rest_framework.decorators import api_view

from users.serializers import GroupSerializer, UserSerializer


class UserViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    queryset = User.objects.all().order_by("-date_joined")
    serializer_class = UserSerializer


class GroupViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    queryset = Group.objects.all().order_by("name")
    serializer_class = GroupSerializer


@api_view(["POST"])
def register(request):
    user_data = UserSerializer(data=request.data)

    if user_data.is_valid():
        User.objects.create_user(**user_data.validated_data)
        return Response(request.data, status=status.HTTP_201_CREATED)
    else:
        return Response([], status=status.HTTP_400_BAD_REQUEST)


class LoginView(KnoxLoginView):
    serializer_class = UserSerializer
    permission_classes = [permissions.AllowAny]

    def post(self, request, format=None):
        serializer = AuthTokenSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        user = serializer.validated_data["user"]
        login(request, user)
        return super(LoginView, self).post(request, format=None)
