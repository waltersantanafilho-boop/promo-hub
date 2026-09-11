<div class="alert alert-warning" role="alert">
    <strong>Somente parâmetros não sensíveis.</strong> Nunca informe secrets, tokens, senhas, chaves de API ou outras credenciais. Credenciais devem ficar em variáveis de ambiente e arquivos de configuração protegidos.
</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label admin-form-label">Loja</label>
        <input class="form-control" value="{{ $store->name }}" readonly aria-label="Loja">
    </div>
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="type">Tipo</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('type')]) id="type" name="type" required>
            <option value="">Selecione</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $source->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="provider_key">Chave do provider <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control', 'is-invalid' => $errors->has('provider_key')]) id="provider_key" name="provider_key" value="{{ old('provider_key', $source->provider_key) }}" maxlength="255" placeholder="exemplo_api">
        @error('provider_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Identifica a implementação no sistema; não é uma chave de API.</div>
    </div>
    <div class="col-12">
        <label class="form-label admin-form-label" for="config">Configuração JSON <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <textarea @class(['form-control font-monospace', 'is-invalid' => $errors->has('config')]) id="config" name="config" rows="10" maxlength="50000" placeholder='{"feed_url": "https://exemplo.com/feed.json", "timeout": 30}'>{{ old('config', $configJson ?? '') }}</textarea>
        @error('config')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Aceita apenas um objeto ou array JSON com parâmetros não sensíveis.</div>
    </div>
    <div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $source->exists ? $source->is_active : true))><label class="form-check-label" for="is_active">Fonte ativa</label></div></div>
</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a><button class="btn btn-primary" type="submit">Salvar fonte</button></div>
