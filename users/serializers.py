from django.contrib.auth import authenticate
from django.contrib.auth.models import User, Group
from rest_framework import serializers

from movie_manager.serializers import MovieListSerializer
from users.models import UserProfile


class UserSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = User
        fields = ["url", "username", "email", "groups"]


class UserProfileSerializer(serializers.HyperlinkedModelSerializer):
    name = serializers.SerializerMethodField()
    username = serializers.SerializerMethodField()
    date_joined = serializers.SerializerMethodField()
    lists = MovieListSerializer(many=True, read_only=True)

    class Meta:
        model = UserProfile
        fields = ["name", "username", "date_joined", "lists"]

    def get_name(self, obj):
        return obj.name or ""

    def get_username(self, obj):
        return obj.user.username

    def get_date_joined(self, obj):
        return obj.user.date_joined


class GroupSerializer(serializers.HyperlinkedModelSerializer):
    class Meta:
        model = Group
        fields = ["url", "name"]


class AuthSerializer(serializers.Serializer):
    username = serializers.CharField()
    password = serializers.CharField(
        style={"input_type": "password"}, trim_whitespace=False
    )

    def validate(self, attrs):
        username = attrs.get("username")
        password = attrs.get("password")

        user = authenticate(
            request=self.context.get("request"),
            username=username,
            password=password,
        )

        if not user:
            msg = f"Invalid username or password."
            raise serializers.ValidationError(msg, code="authentication")

        attrs["user"] = user
        return
