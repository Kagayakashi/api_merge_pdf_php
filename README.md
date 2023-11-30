JSON API сервис.
Пример отправки запроса:
```
[
    {
        "base64": "base64_big_string1......",
        "name": "myfilename1.pdf"
    },
    {
        "base64": "base64_big_string2......",
        "name": "myfilename2.pdf"
    }
]
```


Пример получения ответа на запрос:
```
{
    "success": true,
    "base64": "base64_big_string......",
    "name": "my-merged-file.pdf",
}
```
