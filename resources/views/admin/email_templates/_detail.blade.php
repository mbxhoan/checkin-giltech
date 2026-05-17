<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>CKEditor 5 Sample</title>
		<link rel="stylesheet" href="{{ asset('vendor/ckeditor/style.css') }}">
		<link rel="stylesheet" href="{{ asset('offlines/offline-css/45.0.0-ckeditor5.css') }}" crossorigin>
		<link rel="stylesheet" href="{{ asset('offlines/offline-css/45.0.0-ckeditor5-premium-features.css') }}" crossorigin>
	</head>
	<body>
		<div class="main-container">
			<div
				class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-fullscreen"
				id="editor-container"
			>
				<div class="editor-container__editor">
                    <div id="editor">

                    </div>
                </div>

                <div id="html" style="display: none;">
                    {!! $object['HtmlBody'] !!}
                </div>
			</div>
		</div>
		<script src="{{ asset('offlines/offline-js/45.0.0-ckeditor5.umd.js') }}" crossorigin></script>
		<script src="{{ asset('offlines/offline-js/45.0.0-ckeditor5-premium-features.umd.js') }}" crossorigin></script>
		<script src="{{ asset('offlines/offline-js/45.0.0-vi.umd.js') }}" crossorigin></script>
		<script src="{{ asset('offlines/offline-js/45.0.0-premium-vi.umd.js') }}" crossorigin></script>
		<script src="{{ asset('offlines/offline-js/2.6.1-ckbox.js') }}" crossorigin></script>
		<script src="{{ asset('vendor/ckeditor/main.js') }}"></script>
	</body>
</html>
