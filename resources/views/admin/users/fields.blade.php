<label for="{{ $formId }}-name">{{ __('users.name') }}</label>
<input id="{{ $formId }}-name" name="name" type="text" maxlength="255" value="{{ old('name', $editing ? $user->name : '') }}" required>

<label for="{{ $formId }}-first-name">{{ __('users.first_name') }} ({{ __('users.optional') }})</label>
<input id="{{ $formId }}-first-name" name="first_name" type="text" maxlength="255" value="{{ old('first_name', $editing ? $user->first_name : '') }}">

<label for="{{ $formId }}-role">{{ __('users.role') }}</label>
<select id="{{ $formId }}-role" name="role" required>
    @foreach(['locataire' => __('users.tenant'), 'admin' => __('users.admin')] as $value => $label)
        <option value="{{ $value }}" @selected(old('role', $editing ? $user->role : 'locataire') === $value)>{{ $label }}</option>
    @endforeach
</select>

<input type="hidden" name="is_active" value="0">
<label class="users-checkbox" for="{{ $formId }}-active">
    <input id="{{ $formId }}-active" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $editing ? $user->is_active : true))>
    {{ __('users.active') }}
</label>
