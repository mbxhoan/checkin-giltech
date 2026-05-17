@if (!empty(config("info.tutors.{$key}")) && count(config("info.tutors.{$key}")))
    <a href="" class="text-sm text-warning btn-tutor"
        title="{{ config("info.tutors.{$key}.title") ?? "" }}"
        data-bs-toggle="modal"
        data-bs-target="#{{ $key }}"
    >
        <x-icon name="circle-question" prefix="fa-solid fa-beat-fade" />
    </a>
    @include("components.modal-content", [
        "modalId"           => $key,
        "modalClass"        => "modal-lg modal-dialog-scrollable",
        "modalBodyClass"    => "text-sm fw-normal",
        "title"             => config("info.tutors.{$key}.title") ?? "",
        "content"           => config("info.tutors.{$key}.content") ?? null
    ])
@endif
