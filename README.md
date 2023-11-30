#JSON API examples.

## Request
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

## Response
```
{
    "success": true,
    "base64": "base64_big_string......",
    "name": "my-merged-file.pdf",
}
```
