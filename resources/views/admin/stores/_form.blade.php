<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label admin-form-label" for="name">Nome</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('name')]) id="name" name="name" value="{{ old('name', $store->name) }}" maxlength="255" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label admin-form-label" for="slug">Slug</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('slug')]) id="slug" name="slug" value="{{ old('slug', $store->slug) }}" maxlength="255" placeholder="gerado-a-partir-do-nome">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Deixe em branco para gerar a partir do nome.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="logo_url">URL do logo</label>
        <input type="url" @class(['form-control', 'is-invalid' => $errors->has('logo_url')]) id="logo_url" name="logo_url" value="{{ old('logo_url', $store->logo_url) }}" maxlength="255" placeholder="https://exemplo.com/logo.png" required>
        @error('logo_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="website_url">URL do site</label>
        <input type="url" @class(['form-control', 'is-invalid' => $errors->has('website_url')]) id="website_url" name="website_url" value="{{ old('website_url', $store->website_url) }}" maxlength="255" placeholder="https://exemplo.com" required>
        @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $store->exists ? $store->is_active : true))><label class="form-check-label" for="is_active">Loja ativa</label></div></div>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a><button class="btn btn-primary" type="submit">Salvar loja</button></div>
