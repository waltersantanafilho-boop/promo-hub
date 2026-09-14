<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label admin-form-label" for="name">Nome</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('name')]) id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label admin-form-label" for="slug">Slug</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('slug')]) id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="255" placeholder="gerado-a-partir-do-nome">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Deixe em branco para gerar a partir do nome.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label admin-form-label" for="product_group_id">Grupo <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <select @class(['form-select', 'is-invalid' => $errors->has('product_group_id')]) id="product_group_id" name="product_group_id"><option value="">Sem grupo</option>@foreach ($productGroups as $productGroup)<option value="{{ $productGroup->id }}" @selected((string) old('product_group_id', $product->product_group_id) === (string) $productGroup->id)>{{ $productGroup->name }}</option>@endforeach</select>
        @error('product_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label admin-form-label" for="category_id">Categoria</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('category_id')]) id="category_id" name="category_id" required><option value="">Selecione</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label admin-form-label" for="brand_id">Marca</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('brand_id')]) id="brand_id" name="brand_id" required><option value="">Selecione</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>@endforeach</select>
        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="gtin">GTIN/EAN <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control', 'is-invalid' => $errors->has('gtin')]) id="gtin" name="gtin" value="{{ old('gtin', $product->gtin) }}" maxlength="14" inputmode="numeric">
        @error('gtin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="canonical_image_url">Imagem canônica <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control', 'is-invalid' => $errors->has('canonical_image_url')]) type="url" id="canonical_image_url" name="canonical_image_url" value="{{ old('canonical_image_url', $product->canonical_image_url) }}" maxlength="255" placeholder="https://">
        @error('canonical_image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="description">Descrição <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <textarea @class(['form-control', 'is-invalid' => $errors->has('description')]) id="description" name="description" rows="5" maxlength="20000">{{ old('description', $product->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="attributes">Atributos JSON <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <textarea @class(['form-control font-monospace', 'is-invalid' => $errors->has('attributes')]) id="attributes" name="attributes" rows="8" maxlength="50000" placeholder='{"storage": "128GB", "color": "Preto"}'>{{ old('attributes', $attributesJson ?? '') }}</textarea>
        @error('attributes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Use um objeto ou array JSON válido.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label admin-form-label" for="status">Status</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $product->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label admin-form-label" for="merged_into_id">Destino da mesclagem <span class="fw-normal text-body-secondary">(somente para status mesclado)</span></label>
        <select @class(['form-select', 'is-invalid' => $errors->has('merged_into_id')]) id="merged_into_id" name="merged_into_id"><option value="">Selecione o produto de destino</option>@foreach ($mergeTargets as $target)<option value="{{ $target->id }}" @selected((string) old('merged_into_id', $product->merged_into_id) === (string) $target->id)>{{ $target->name }}</option>@endforeach</select>
        @error('merged_into_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a><button class="btn btn-primary" type="submit">Salvar produto</button></div>
