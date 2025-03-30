<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentationTitle }}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
        /* Base Styles */
        html {
            box-sizing: border-box;
            overflow-y: scroll;
            font-size: 14px;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #f5f5f5;
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.5;
            color: #2a2a2a;
        }

        /* Swagger UI Customizations */
        #swagger-ui {
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px;
        }

        .swagger-ui .info {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .swagger-ui .info .title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        /* Enhanced Card Styling for Operations */
        .swagger-ui .opblock {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.03);
            margin: 0 0 20px 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .swagger-ui .opblock:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.08);
        }

        .swagger-ui .opblock-summary {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
        }

        .swagger-ui .opblock-summary-method {
            font-size: 0.875rem;
            padding: 6px 10px;
            border-radius: 4px;
            min-width: 60px;
            text-align: center;
        }

        .swagger-ui .opblock-summary-path {
            font-size: 0.9375rem;
            color: #2a2a2a;
        }

        .swagger-ui .opblock-summary-description {
            font-size: 0.8125rem;
            color: #666;
        }

        .swagger-ui .opblock-body {
            padding: 15px;
            font-size: 0.875rem;
        }

        /* Method Colors */
        .swagger-ui .opblock-get {
            border-left: 4px solid #61affe;
        }

        .swagger-ui .opblock-post {
            border-left: 4px solid #49cc90;
        }

        .swagger-ui .opblock-put {
            border-left: 4px solid #fca130;
        }

        .swagger-ui .opblock-delete {
            border-left: 4px solid #f93e3e;
        }

        /* Dark Mode */
        @if(config('l5-swagger.defaults.ui.display.dark_mode'))
            body#dark-mode {
                background: #1a1a1a;
                color: #e0e0e0;
            }

            #dark-mode .info {
                background: #242424;
                box-shadow: 0 2px 4px rgba(255,255,255,0.05);
            }

            #dark-mode .info .title {
                color: #f0f0f0;
            }

            #dark-mode .opblock {
                background: #242424;
                box-shadow: 0 3px 6px rgba(255,255,255,0.03), 0 1px 3px rgba(255,255,255,0.02);
            }

            #dark-mode .opblock:hover {
                box-shadow: 0 5px 12px rgba(255,255,255,0.06);
            }

            #dark-mode .opblock-summary {
                border-bottom: 1px solid #333;
            }

            #dark-mode .opblock-summary-path {
                color: #e0e0e0;
            }

            #dark-mode .opblock-summary-description {
                color: #a0a0a0;
            }

            #dark-mode .opblock-body {
                color: #d0d0d0;
            }

            #dark-mode .opblock-get {
                border-left-color: #61affe;
            }

            #dark-mode .opblock-post {
                border-left-color: #49cc90;
            }

            #dark-mode .opblock-put {
                border-left-color: #fca130;
            }

            #dark-mode .opblock-delete {
                border-left-color: #f93e3e;
            }

            #dark-mode input[type="text"],
            #dark-mode textarea {
                background: #303030;
                color: #e0e0e0;
                border: 1px solid #404040;
                font-family: 'Georgia', serif;
                font-size: 0.875rem;
            }
        @endif
    </style>
</head>

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
    <div id="swagger-ui"></div>

    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
    <script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.onload = function() {
            // Fetch CSRF cookie on page load
            fetch('/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'include' // Ensure cookies are sent and received
            }).then(() => {
                console.log('CSRF cookie fetched successfully');
            }).catch(err => {
                console.error('Failed to fetch CSRF cookie:', err);
            });

            const urls = [];

            @foreach($urlsToDocs as $title => $url)
                urls.push({name: "{{ $title }}", url: "{{ $url }}"});
            @endforeach

            const ui = SwaggerUIBundle({
                dom_id: '#swagger-ui',
                urls: urls,
                "urls.primaryName": "{{ $documentationTitle }}",
                operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
                configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
                validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
                oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",
                requestInterceptor: function(request) {
                    request.headers['accept'] = 'application/json';
                    request.headers['X-XSRF-TOKEN'] = Cookies.get('XSRF-TOKEN');
                    return request;
                },
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                docExpansion: "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
                deepLinking: true,
                filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
                persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}"
            });

            window.ui = ui;

            @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
                ui.initOAuth({
                    usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
                });
            @endif
        }
    </script>
</body>
</html>