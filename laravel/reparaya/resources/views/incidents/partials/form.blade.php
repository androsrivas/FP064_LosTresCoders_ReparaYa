{{-- Alta de incidencia (dashboard de particular y gestora) --}}
<form method="POST" action="{{ route('incidents.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="speciality_id" class="form-label">Especialidad</label>
        <select name="speciality_id" id="speciality_id" required @class(['form-select', 'is-invalid' => $errors->has('speciality_id')])>
            <option value="">-- Selecciona --</option>
            @foreach ($specialities as $speciality)
                <option value="{{ $speciality->id }}" @selected(old('speciality_id') == $speciality->id)>
                    {{ $speciality->name }}
                </option>
            @endforeach
        </select>
        @error('speciality_id') <div class="invalid-feedback">
            {{ $message }} 
        </div> @enderror
    </div>

    {{-- Solo la gestora asocia la incidencia a una de sus comunidades --}}
    @if (auth()->user()->hasRole(\App\Enums\UserRole::PropertyManager))
        <div class="mb-3">
            <label for="community_id" class="form-label">Comunidad</label>
            <select name="community_id" id="community_id" required @class(['form-select', 'is-invalid' => $errors->has('community_id')])>
                <option value="">-- Selecciona --</option>
                @foreach ($communities as $community)
                    <option value="{{ $community->id }}" @selected(old('community_id') == $community->id)>
                        {{ $community->name }}
                    </option>
                @endforeach
            </select>
            @error('community_id') <div class="invalid-feedback">
                {{ $message }}
            </div> @enderror
        </div>
    @endif

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="urgency" class="form-label">Urgencia</label>
            <select name="urgency" id="urgency" required @class(['form-select', 'is-invalid' => $errors->has('urgency')])>
                @foreach(\App\Enums\Urgency::cases() as $urgency)
                    <option value="{{ $urgency->value }}"  @selected(old('urgency') === $urgency->value)>
                        {{ $urgency->value }}
                    </option>
                @endforeach
            </select>
            @error('urgency') <div class="invalid-feedback">
                {{ $message }}
            </div> @enderror
        </div>

        <div class="col-sm-6 mb-3">
            <label for="service_date" class="form-label">Fecha y hora del servicio</label>
            <input type="datetime-local" id="service_date" name="service_date" required
                value="{{ old('service_date') }}"
                @class(['form-control', 'is-invalid' => $errors->has('service_date')])>
            @error('service_date') <div class="invalid-feedback">
                {{ $message }}
            </div> @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Dirección</label>
        <input type="text" id="address" name="address" required maxlength="255"
            value="{{ old('address') }}" autocomplete="street-address"
            placeholder="Calle, número, piso, ciudad"
            @class(['form-control', 'is-invalid' => $errors->has('address')])>
            @error('address') <div class="invalid-feedback">
            {{ $message }}
        </div> @enderror
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Descripción</label>
        <textarea name="description" id="description" rows="3" required maxlength="1000" placeholder="Describe la avería (mínimo 10 caracteres)" 
            @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description') }}</textarea>
        @error('description') <div class="invalid-feedback">
            {{ $message }}
        </div> @enderror
    </div>

    <button type="submit" class="btn btn-yellow w-100">Enviar solicitud</button>
</form>