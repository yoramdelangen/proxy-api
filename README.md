# Proxy API

A little webserver to reverse proxy traffic. Mainly usefull for development
purposes. Previous version was created with PHP, now using GO.

## Requests

Only route we need:

```
GET /p?url={url}
```

> This route supports Any type of request method. Maybe we should limit it
> in the future.
