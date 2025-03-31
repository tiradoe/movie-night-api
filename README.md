Django Docker Template
=

Starter template for Django in a Docker container. Includes Postgres for the database and Django Rest Framework configured with Knox token authentication enabled.

Dependencies
=
- Docker ([running as non-root user](https://docs.docker.com/engine/install/linux-postinstall/))
- Git

How to use
=
1. Clone the project and enter the project directory
   1. `git clone https://edbuildsthings.com/tiradoe/django-docker-template.git your_project_name`
   2. `cd django-docker-template`
2. Run the setup script
   1. `./firstRun.sh` and enter information when prompted
   2. If something goes wrong, running `git checkout .` will restore any changes but you'll need to manually delete the project directory that was created

That's it!  You should now have a basic project running with documentation at `http://localhost:8000`

Note: This uses common Linux utis so it should work on just about any Linux distro (including Windows WSL) and _probably_ MacOS too.  However, this has only been tested on Fedora Linux.  Please submit an issue if you run into any problems and I'll see what I can do.
