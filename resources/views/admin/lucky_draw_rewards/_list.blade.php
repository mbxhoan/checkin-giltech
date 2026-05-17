@if (!empty($luckyDrawRewards) && $luckyDrawRewards->count())
    <input type="hidden" id="luckyDrawId" value="{{ $luckyDraw->id }}">
    <div class="row mt-2 text-xs border-bottom pb-2 mx-1">
        <div class="col-md-4 fw-bold">
            THÔNG TIN GIẢI
        </div>
        <div class="col-md-4 fw-bold">
            ABC
        </div>
        <div class="col-md-2 fw-bold text-center">
            ẢNH
        </div>
        <div class="col-md-2 fw-bold text-center"></div>
    </div>
    @foreach ($luckyDrawRewards as $index => $luckyDrawReward)
        <div class="row align-items-center text-xs mt-2">
            <div class="col-md-4">
                <div class="">
                    <span class="fw-bold">
                        STT:
                    </span>
                    {{ $luckyDrawReward->order }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        Tên thứ tự:
                    </span>
                    {{ $luckyDrawReward->order_name }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        Mã:
                    </span>
                    {{ $luckyDrawReward->code }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        Tên:
                    </span>
                    {{ $luckyDrawReward->name }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        Thời gian quay (s):
                    </span>
                    {{ $luckyDrawReward->time }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        Giá trị:
                    </span>
                    {{ $luckyDrawReward->value }}
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" class="data-assignee_ids"
                    data-url="{{ route('admin.lucky_draw_rewards.update-assignees', $luckyDrawReward) }}"
                    data-reward_id="{{ $luckyDrawReward->id }}"
                    data-max="{{ (int) ($luckyDrawReward->value ?? 0) }}"
                >
                @include('components.select', [
                    'id'            => 'assignee_ids_' . $luckyDrawReward->id,
                    'fieldName'     => 'assignee_ids',
                    'multiple'      => true,
                    'formClass'     => 'w-100 input-assignee_ids',
                    'options'       => !empty($assignees) ? $assignees : [],
                    'selected'      => $luckyDrawReward->assignees?->pluck('id')->toArray() ?? [],
                ])
            </div>
            <div class="col-md-2 text-center">
                <img src="{{ $luckyDrawReward->img_link }}" class="" alt="{{ $luckyDrawReward->name }}"
                    style="
                        max-width: 100px;
                        /* width: ; */
                        height: 100px;
                    "
                >
            </div>
            <div class="col-md-2">
                @if ($luckyDrawReward->is_given)
                    <div class="mb-2">
                        <a id="btn-cancel-reward-{{ $luckyDrawReward->id }}"
                            class="btn-cancel-reward btn btn-xs btn-danger"
                            data-id="{{ $luckyDrawReward->id }}"
                            data-url="{{ route('admin.lucky-draw.cancel-reward') }}"
                            title="Huỷ giải"
                        >
                            <i class="bx bx-x-circle"></i>
                        </a>
                    </div>
                @else
                    @include('admin.lucky_draw_rewards._modal-upsert', [
                        'model'     => $luckyDrawReward,
                        'modalId'   => "updateRewardModal-{$luckyDrawReward->id}",
                        'text'      => 'Cập nhật giải thưởng',
                        'textIcon'  => '<i class="fa-solid fa-edit"></i>',
                        'route'     => route('admin.lucky_draw_rewards.update', $luckyDrawReward),
                    ])
                @endif
                <form action="{{ route('admin.lucky_draw_rewards.destroy', $luckyDrawReward) }}"
                    class="form-inline"
                    method="POST"
                    onsubmit="return confirm('Bạn có chắc muốn xóa giải này không?');"
                >
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger btn-xs" title="Xóa giải">
                        <x-icon name="trash" />
                    </button>
                </form>
            </div>
        </div>
    @endforeach
@else
    <div class="fst-italic pt-2">
        Chưa có giải thưởng
    </div>
@endif
