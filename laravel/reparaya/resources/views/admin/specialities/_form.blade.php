<div class="mb-3">
    <label for="name" class="form-label">
        Nombre de la especialidad <span class="text-danger">*</span>
    </label>
    <input type="text" id="name" name="name"
        value="{{ old('name', $speciality->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descripción</label>
    <textarea id="description" name="description" rows="3"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $speciality->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="responsibilities" class="form-label">Responsabilidades</label>
    <textarea id="responsibilities" name="responsibilities" rows="3"
        class="form-control @error('responsibilities') is-invalid @enderror">{{ old('responsibilities', $speciality->responsibilities ?? '') }}</textarea>
    @error('responsibilities')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <label for="base_salary" class="form-label">
            Salario base (€) <span class="text-danger">*</span>
        </label>
        <input type="number" step="0.01" min="0" id="base_salary" name="base_salary"
            value="{{ old('base_salary', $speciality->base_salary ?? '') }}"
            class="form-control @error('base_salary') is-invalid @enderror" required>
        @error('base_salary')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="base_price" class="form-label">
            Precio base de referencia (€) <span class="text-danger">*</span>
        </label>
        <input type="number" step="0.01" min="0" id="base_price" name="base_price"
            value="{{ old('base_price', $speciality->base_price ?? '') }}"
            class="form-control @error('base_price') is-invalid @enderror" required>
        @error('base_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-yellow">{{ $submitLabel }}</button>
    <a href="{{ route('admin.specialities.index') }}" class="btn btn-outline-light">Cancelar</a>
</div>