<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label admin-form-label" for="name">Nome</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('name')]) id="name" name="name" value="{{ old('name', $productGroup->name) }}" maxlength="255" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label admin-form-label" for="slug">Slug</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('slug')]) id="slug" name="slug" value="{{ old('slug', $productGroup->slug) }}" maxlength="255" placeholder="gerado-a-partir-do-nome">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Deixe em branco para gerar a partir do nome.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="category_id">Categoria</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('category_id')]) id="category_id" name="category_id" required>
            <option value="">Selecione</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $productGroup->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="brand_id">Marca</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('brand_id')]) id="brand_id" name="brand_id" required>
            <option value="">Selecione</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected((string) old('brand_id', $productGroup->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="description">Descrição <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <textarea @class(['form-control', 'is-invalid' => $errors->has('description')]) id="description" name="description" rows="5" maxlength="10000">{{ old('description', $productGroup->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $productGroup->exists ? $productGroup->is_active : true))><label class="form-check-label" for="is_active">Grupo ativo</label></div>
    </div>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a><button class="btn btn-primary" type="submit">Salvar grupo</button></div>
