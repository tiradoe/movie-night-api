from knox.auth import TokenAuthentication
from django.contrib.auth.models import Group
from rest_framework import viewsets, permissions, status

from users.serializers import GroupSerializer


class GroupViewSet(viewsets.ModelViewSet):
    authentication_classes = [TokenAuthentication]
    permission_classes = [permissions.IsAuthenticated]

    queryset = Group.objects.all().order_by("name")
    serializer_class = GroupSerializer
