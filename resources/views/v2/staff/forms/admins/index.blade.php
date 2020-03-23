@extends('v2.layouts.no_drawer')

@section('title', 'フォーム管理者設定')

@section('content')
<app-container>
    <form method="POST" action="#">
        @method('patch')
        @csrf
        <list-view>
            <template v-slot:title>フォームID：{{ $form->id }}「{{ $form->name }}」 ー 管理者設定</template>
            <list-view-form-group label-for="admin_ids">
                <template v-slot:label>管理者</template>
                <template v-slot:description>学籍番号で指定。改行で複数人指定できます。</template>
                <textarea
                    name="admin_ids"
                    class="form-control @error('admin_ids') is-invalid @enderror"
                    rows="5"
                >
                </textarea>
                @error('admin_ids')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>
            <list-view-form-group class="text-center">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </list-view-form-group>
        </list-view>
    </form>

    @if (empty($admins))
    <list-view>
        <template v-slot:title>個別設定</template>
        @foreach ($admins as $admin)
        <list-view-item href="#">
            <template v-slot:title>{{ $admin->student_id }} {{ $admin->name }}</template>
        </list-view-item>
        @endforeach
    </list-view>
    @endif
</app-container>
@endsection
