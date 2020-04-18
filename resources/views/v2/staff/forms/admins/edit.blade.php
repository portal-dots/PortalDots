@extends('v2.layouts.no_drawer')

@section('title', 'フォーム管理者設定')
    
@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.forms.admin.index', ['form' => $form]) }}">
        戻る
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
        <form method="POST" action="#">
            @method('PATCH')
            @csrf
            <list-view>
                <template v-slot:title>フォームID：{{ $form->id }}「{{ $form->name }}」 ー
                    {{ $user->student_id }}：{{ $user->name }}</template>
                <list-view-form-group label-for="receive_email">
                    <template v-slot:label>メール受信設定</template>
                    <template v-slot:description>このフォームに回答が追加・編集された場合にメールが送信されます</template>
                    <label><input type="radio" name="receive_email" value="false"> メールを受け取らない</label>
                    <label><input type="radio" name="receive_email" value="true"> メールを受け取る</label>
                    @error('receive_email')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                {{-- 下の２項目は所有者にのみ表示する --}}
                <list-view-form-group label-for="writable">
                    <template v-slot:label>書き込み権限</template>
                    <template v-slot:description>このユーザーはフォームの設問を変更することが可能になります</template>
                    <label><input type="radio" name="writable" value="false"> フォームの閲覧のみ</label>
                    <label><input type="radio" name="writable" value="true"> フォームの閲覧・編集</label>
                    @error('writable')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="owner">
                    <template v-slot:label>フォーム所有者</template>
                    <template v-slot:description>このユーザーはフォームの他の管理者の設定を変更することができます</template>
                    <label><input type="checkbox" name="owner" value="true"> 所有者として設定</label>
                    @error('owner')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                {{-- ここまで所有者のみ表示する項目 --}}
            </list-view>
            <div class="text-center pt-spacing-md">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </form>
    </app-container>
@endsection
