<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label admin-form-label" for="name">Nome</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('name')]) id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="255" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label admin-form-label" for="slug">Slug</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('slug')]) id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="255" placeholder="gerado-a-partir-do-nome">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Deixe em branco para gerar a partir do nome.</div>
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="parent_id">Categoria pai</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('parent_id')]) id="parent_id" name="parent_id">
            <option value="">Sem categoria pai</option>
            @foreach ($parentCategories as $parentCategory)
                <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parentCategory->id)>{{ $parentCategory->name }}</option>
            @endforeach
        </select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))>
            <label class="form-check-label" for="is_active">Categoria ativa</label>
        </div>
    </div>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4">
    <a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a>
    <button class="btn btn-primary" type="submit">Salvar categoria</button>
</div>
