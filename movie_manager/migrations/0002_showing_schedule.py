# Generated by Django 5.1.4 on 2025-04-21 01:01

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('movie_manager', '0001_initial'),
    ]

    operations = [
        migrations.AddField(
            model_name='showing',
            name='schedule',
            field=models.ForeignKey(default=1, on_delete=django.db.models.deletion.CASCADE, to='movie_manager.schedule'),
            preserve_default=False,
        ),
    ]
