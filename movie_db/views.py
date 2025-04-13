from django.http import JsonResponse
from movie_db.db_providers.omdb import OMDb


def omdb_search(request):
    query = request.GET.get("q")
    if not query:
        return JsonResponse({"Error": "Missing query"}, status=400)

    search_type = request.GET.get("type")
    omdb = OMDb()
    return JsonResponse(omdb.search(query, {"type": search_type}), safe=False)
