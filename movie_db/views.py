from django.http import JsonResponse
from movie_db.db_providers.omdb import OMDb


def omdb_search(request):
    print("ENTERING MOVIE SEARCH")
    query = request.GET.get("q")
    if not query:
        return JsonResponse({"Error": "Missing query"}, status=400)

    search_type = request.GET.get("type")
    omdb = OMDb()

    results = omdb.search(query, {"type": search_type})
    if "error" in results:
        return parse_error(results)

    return JsonResponse(results, safe=False)


def parse_error(results):
    error_json = results["error"]
    if "Error" in error_json and error_json["Error"] == "Movie not found!":
        return JsonResponse({}, status=404)
    else:
        return JsonResponse("Error while searching for movie.", status=500)