@extends('layouts.master')
@section('title')
    @lang('translation.list-institutions')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Tables
        @endslot
        @slot('title')
            Recintos
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Recintos</h4>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-line me-1"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-1"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="ri-alert-line me-1"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-1"></i>
                            <strong>Por favor corrija los siguientes errores:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <div class="listjs-table" id="institutionList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                        id="create-btn" data-bs-target="#showModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Agregar
                                    </button>
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-download-line align-bottom me-1"></i> Excel
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('institutions.export') }}">
                                                    <i class="ri-file-excel-line me-2"></i> Exportar Datos
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('institutions.template') }}">
                                                    <i class="ri-file-download-line me-2"></i> Descargar Plantilla
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                                    <i class="ri-file-upload-line me-2"></i> Importar Datos
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-soft-danger" id="delete-multiple-btn" onclick="deleteMultiple()" style="display:none;">
                                        <i class="ri-delete-bin-2-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control search" placeholder="Buscar institución...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort" data-sort="institution_code">Código</th>
                                        <th class="sort" data-sort="institution_name">Institución</th>
                                        <th class="sort" data-sort="municipality">Municipio</th>
                                        <th class="sort" data-sort="citizens">Ciudadanos</th>
                                        <th class="sort" data-sort="actas">Actas</th>
                                        <th class="sort" data-sort="status">Estado</th>
                                        <th class="sort" data-sort="action">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @foreach($institutions as $institution)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input child-checkbox" type="checkbox" name="chk_child" value="{{ $institution->id }}">
                                                </div>
                                            </th>
                                            <td class="institution_code">
                                                <span class="badge bg-info-subtle text-info">{{ $institution->code ?? 'N/A' }}</span>
                                            </td>
                                            <td class="institution_name">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h5 class="fs-14 mb-1">{{ $institution->name }}</h5>
                                                        @if($institution->registered_citizens)
                                                            <small class="text-muted">{{ number_format($institution->registered_citizens) }} ciudadanos</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- <td class="institution_address">{{ Str::limit($institution->address ?? 'N/A', 50) }}</td> -->
                                            <td class="municipality">
                                                {{ $institution->locality->municipality->name ?? 'N/A' }}
                                                <small class="d-block text-muted">
                                                    {{ $institution->locality->name ?? '' }}
                                                </small>
                                            </td>
                                            <td class="citizens">
                                                <span class="fw-semibold">{{ number_format($institution->registered_citizens ?? 0) }}</span>
                                            </td>
                                            <td class="actas">
                                                <div class="d-flex gap-1">
                                                    @if($institution->total_computed_records > 0)
                                                        <span class="badge bg-primary-subtle text-primary" title="Computadas">
                                                            C: {{ $institution->total_computed_records }}
                                                        </span>
                                                    @endif
                                                    @if($institution->total_annulled_records > 0)
                                                        <span class="badge bg-danger-subtle text-danger" title="Anuladas">
                                                            A: {{ $institution->total_annulled_records }}
                                                        </span>
                                                    @endif
                                                    @if($institution->total_enabled_records > 0)
                                                        <span class="badge bg-success-subtle text-success" title="Habilitadas">
                                                            H: {{ $institution->total_enabled_records }}
                                                        </span>
                                                    @endif
                                                    @if(!$institution->total_computed_records && !$institution->total_annulled_records && !$institution->total_enabled_records)
                                                        <span class="text-muted">Sin datos</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="status">
                                                <span class="badge bg-{{ $institution->active ? 'success' : 'danger' }}-subtle text-{{ $institution->active ? 'success' : 'danger' }}">
                                                    {{ $institution->active ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="{{ $institution->id }}"
                                                            data-name="{{ $institution->name }}"
                                                            data-code="{{ $institution->code }}"
                                                            data-registered-citizens="{{ $institution->registered_citizens }}"
                                                            data-address="{{ $institution->address }}"
                                                            data-department-id="{{ $institution->locality->municipality->province->department->id ?? '' }}"
                                                            data-province-id="{{ $institution->locality->municipality->province->id ?? '' }}"
                                                            data-municipality-id="{{ $institution->locality->municipality->id ?? '' }}"
                                                            data-locality-id="{{ $institution->locality_id }}"
                                                            data-district-id="{{ $institution->district_id }}"
                                                            data-zone-id="{{ $institution->zone_id }}"
                                                            data-total-computed-records="{{ $institution->total_computed_records }}"
                                                            data-total-annulled-records="{{ $institution->total_annulled_records }}"
                                                            data-total-enabled-records="{{ $institution->total_enabled_records }}"
                                                            data-active="{{ $institution->active ? '1' : '0' }}"
                                                            data-update-url="{{ route('institutions.update', $institution->id) }}">
                                                            <i class="ri-pencil-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="{{ $institution->id }}"
                                                            data-name="{{ $institution->name }}"
                                                            data-delete-url="{{ route('institutions.destroy', $institution->id) }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            @if($institutions->isEmpty())
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay recintos registrados en el sistema.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <div class="pagination-wrap hstack gap-2">
                                <a class="page-item pagination-prev disabled" href="javascript:void(0);">
                                    Anterior
                                </a>
                                <ul class="pagination listjs-pagination mb-0"></ul>
                                <a class="page-item pagination-next" href="javascript:void(0);">
                                    Siguiente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Nueva Institución/Recinto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="institutionForm" method="POST" action="{{ route('institutions.store') }}" class="tablelist-form" autocomplete="off">
                    @csrf
                    <input type="hidden" id="method_field" name="_method" value="">
                    <input type="hidden" id="institution_id" name="id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line align-middle me-1"></i> 
                            El código de la institución se generará automáticamente si no se especifica.
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name-field" class="form-label">Nombre de la Institución/Recinto <span class="text-danger">*</span></label>
                                    <input type="text" id="name-field" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        placeholder="Ingrese el nombre de la institución" value="{{ old('name') }}" required />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="code-field" class="form-label">Código</label>
                                    <input type="text" id="code-field" name="code" 
                                        class="form-control @error('code') is-invalid @enderror" 
                                        placeholder="Se genera automáticamente" value="{{ old('code') }}" />
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="address-field" class="form-label">Dirección</label>
                                    <textarea id="address-field" name="address" class="form-control @error('address') is-invalid @enderror" 
                                        placeholder="Ingrese la dirección de la institución" rows="2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="registered-citizens-field" class="form-label">Ciudadanos Habilitados</label>
                                    <input type="number" id="registered-citizens-field" name="registered_citizens" 
                                        class="form-control @error('registered_citizens') is-invalid @enderror" 
                                        placeholder="Número de ciudadanos" value="{{ old('registered_citizens') }}" min="0" />
                                    @error('registered_citizens')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ri-map-pin-line me-1"></i>Ubicación Geográfica
                                </h6>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="department-field" class="form-label">Departamento <span class="text-danger">*</span></label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" name="department_id" id="department-field"
                                        data-url="{{ url('institutions/provinces') }}" required>
                                        <option value="">Seleccione Departamento</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione un departamento.</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="province-field" class="form-label">Provincia <span class="text-danger">*</span></label>
                                    <select class="form-control @error('province_id') is-invalid @enderror" name="province_id" id="province-field"
                                        data-url="{{ url('institutions/municipalities') }}" required disabled>
                                        <option value="">Seleccione Provincia</option>
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione una provincia.</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="municipality-field" class="form-label">Municipalidad <span class="text-danger">*</span></label>
                                    <select class="form-control @error('municipality_id') is-invalid @enderror" name="municipality_id" id="municipality-field"
                                        data-url="{{ url('institutions/localities') }}" required disabled>
                                        <option value="">Seleccione Municipalidad</option>
                                    </select>
                                    @error('municipality_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione una municipalidad.</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="locality-field" class="form-label">Localidad <span class="text-danger">*</span></label>
                                    <select class="form-control @error('locality_id') is-invalid @enderror" name="locality_id" id="locality-field"
                                        data-url="{{ url('institutions/districts') }}" required disabled>
                                        <option value="">Seleccione Localidad</option>
                                    </select>
                                    @error('locality_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione una localidad.</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="district-field" class="form-label">Distrito (opcional)</label>
                                    <select class="form-control @error('district_id') is-invalid @enderror" name="district_id" id="district-field"
                                        data-url="{{ url('institutions/zones') }}" disabled>
                                        <option value="">Seleccione Distrito</option>
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zone-field" class="form-label">Zona (opcional)</label>
                                    <select class="form-control @error('zone_id') is-invalid @enderror" name="zone_id" id="zone-field" disabled>
                                        <option value="">Seleccione Zona</option>
                                    </select>
                                    @error('zone_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ri-file-list-line me-1"></i>Datos Electorales
                                </h6>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total-computed-records-field" class="form-label">Actas Computadas</label>
                                    <input type="number" id="total-computed-records-field" name="total_computed_records" 
                                        class="form-control @error('total_computed_records') is-invalid @enderror" 
                                        placeholder="0" value="{{ old('total_computed_records', 0) }}" min="0" />
                                    @error('total_computed_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total-annulled-records-field" class="form-label">Actas Anuladas</label>
                                    <input type="number" id="total-annulled-records-field" name="total_annulled_records" 
                                        class="form-control @error('total_annulled_records') is-invalid @enderror" 
                                        placeholder="0" value="{{ old('total_annulled_records', 0) }}" min="0" />
                                    @error('total_annulled_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total-enabled-records-field" class="form-label">Actas Habilitadas</label>
                                    <input type="number" id="total-enabled-records-field" name="total_enabled_records" 
                                        class="form-control @error('total_enabled_records') is-invalid @enderror" 
                                        placeholder="0" value="{{ old('total_enabled_records', 0) }}" min="0" />
                                    @error('total_enabled_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="active-field" name="active" value="1" checked>
                                <label class="form-check-label" for="active-field">
                                    <strong>Institución Activa</strong>
                                    <small class="d-block text-muted">Desactive si la institución no está operativa</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" id="save-btn">
                                <i class="ri-save-line me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                            colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>¿Está seguro?</h4>
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar el Registro? <strong id="delete-institution-name"></strong>?</p>
                            <p class="text-danger mt-2 mb-0"><small>Esta acción no se puede deshacer.</small></p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn w-sm btn-danger">
                                <i class="ri-delete-bin-line me-1"></i>Sí, eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('institutions.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">
                            <i class="ri-file-upload-line me-1"></i>Importar Nuevos Registros
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="import-file" class="form-label">Seleccionar archivo Excel</label>
                            <input class="form-control" type="file" id="import-file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Archivos permitidos: .xlsx, .xls, .csv (máx. 2MB)</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            <strong>Importante:</strong> Asegúrese de que el archivo cumpla con la estructura de la plantilla.
                            <br>
                            <a href="{{ route('institutions.template') }}" class="alert-link">
                                <i class="ri-download-line me-1"></i>Descargar Plantilla
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-upload-line me-1"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Import Errors Modal -->
    @if(session('import_errors'))
    <div class="modal fade" id="importErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="ri-alert-line me-1"></i>Errores de Importación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Se encontraron errores durante la importación:</strong>
                        <br>Los siguientes registros no pudieron ser procesados correctamente.
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach(session('import_errors') as $error)
                        <div class="list-group-item">
                            <i class="ri-error-warning-line text-danger me-2"></i>{{ $error }}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('institutions.template') }}" class="btn btn-info">
                        <i class="ri-download-line me-1"></i>Descargar Plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {    
            var options = {
                valueNames: ['institution_code', 'institution_name', 'institution_address', 'municipality', 'citizens', 'status'],
                page: 10,
                pagination: true
            };
            var institutionList = new List('institutionList', options);

            const checkAll = document.getElementById('checkAll');
            const childCheckboxes = document.querySelectorAll('.child-checkbox');
            const deleteMultipleBtn = document.getElementById('delete-multiple-btn');
            checkAll.addEventListener('change', function() {
                childCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleDeleteButton();
            });
            childCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
                    checkAll.checked = checkedBoxes.length === childCheckboxes.length;
                    checkAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < childCheckboxes.length;
                    toggleDeleteButton();
                });
            });

            function toggleDeleteButton() {
                const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
                deleteMultipleBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
            }

            const departmentSelect = document.getElementById('department-field');
            const provinceSelect = document.getElementById('province-field');
            const municipalitySelect = document.getElementById('municipality-field');
            const localitySelect = document.getElementById('locality-field');
            const districtSelect = document.getElementById('district-field');
            const zoneSelect = document.getElementById('zone-field');
            departmentSelect.addEventListener('change', function () {
                const departmentId = this.value;
                resetDependentSelects([provinceSelect, municipalitySelect, localitySelect, districtSelect, zoneSelect]);                
                if (departmentId) {
                    const url = `${this.dataset.url}/${departmentId}`;
                    loadOptions(url, provinceSelect, 'Seleccione Provincia');
                }
            });
            provinceSelect.addEventListener('change', function () {
                const provinceId = this.value;
                resetDependentSelects([municipalitySelect, localitySelect, districtSelect, zoneSelect]);                
                if (provinceId) {
                    const url = `${this.dataset.url}/${provinceId}`;
                    loadOptions(url, municipalitySelect, 'Seleccione Municipalidad');
                }
            });
            municipalitySelect.addEventListener('change', function () {
                const municipalityId = this.value;
                resetDependentSelects([localitySelect, districtSelect, zoneSelect]);                
                if (municipalityId) {
                    const url = `${this.dataset.url}/${municipalityId}`;
                    loadOptions(url, localitySelect, 'Seleccione Localidad');
                }
            });
            localitySelect.addEventListener('change', function () {
                const localityId = this.value;
                resetDependentSelects([districtSelect, zoneSelect]);                
                if (localityId) {
                    const url = `${this.dataset.url}/${localityId}`;
                    loadOptions(url, districtSelect, 'Seleccione Distrito');
                }
            });
            districtSelect.addEventListener('change', function () {
                const districtId = this.value;
                resetDependentSelects([zoneSelect]);                
                if (districtId) {
                    const url = `${this.dataset.url}/${districtId}`;
                    loadOptions(url, zoneSelect, 'Seleccione Zona');
                }
            });
            function resetDependentSelects(selects) {
                selects.forEach(select => {
                    const firstOption = select.querySelector('option');
                    const originalPlaceholder = firstOption ? firstOption.textContent : 'Seleccionar';
                    select.innerHTML = `<option value="">${originalPlaceholder}</option>`;
                    select.disabled = true;
                });
            }
            async function loadOptions(url, target, placeholder) {
                target.innerHTML = `<option value="">${placeholder} (Cargando...)</option>`;
                target.disabled = true;                
                try {
                    const response = await fetch(url);                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    const data = await response.json();                    
                    if (data.error) {
                        throw new Error(data.error);
                    }                    
                    target.innerHTML = `<option value="">${placeholder}</option>`;                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            target.appendChild(option);
                        });
                    } else {
                        const noDataOption = document.createElement('option');
                        noDataOption.value = "";
                        noDataOption.textContent = "No hay datos disponibles";
                        noDataOption.disabled = true;
                        target.appendChild(noDataOption);
                    }                    
                } catch (error) {
                    console.error('Error loading data from', url, ':', error);                    
                    target.innerHTML = `<option value="">${placeholder}</option>`;
                    const errorOption = document.createElement('option');
                    errorOption.value = "";
                    errorOption.textContent = "Error al cargar datos";
                    errorOption.disabled = true;
                    target.appendChild(errorOption);                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudieron cargar los datos. Por favor, recarga la página.',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } finally {
                    target.disabled = false;
                }
            }

            document.getElementById('create-btn').addEventListener('click', function() {
                document.getElementById('exampleModalLabel').textContent = 'Agregar Recinto';
                document.getElementById('institutionForm').action = "{{ route('institutions.store') }}";
                document.getElementById('method_field').value = '';            
                document.getElementById('institutionForm').reset();
                document.getElementById('active-field').checked = true;
                document.getElementById('institution_id').value = '';
                document.getElementById('name-field').classList.remove('is-invalid');
                document.getElementById('address-field').classList.remove('is-invalid');
                document.getElementById('locality-field').classList.remove('is-invalid');
                document.getElementById('save-btn').innerHTML = '<i class="ri-save-line me-1"></i>Guardar';
                clearValidationErrors();
                resetDependentSelects([provinceSelect, municipalitySelect, localitySelect, districtSelect, zoneSelect]);
            });
            document.querySelectorAll(".edit-item-btn").forEach(btn => {
                btn.addEventListener("click", async function () {
                    document.getElementById('exampleModalLabel').textContent = 'Editar Recinto';
                    document.getElementById('institutionForm').action = this.dataset.updateUrl;
                    document.getElementById('method_field').value = 'PUT';
                    document.getElementById('institution_id').value = this.dataset.id;
                    document.getElementById('save-btn').innerHTML = '<i class="ri-refresh-line me-1"></i>Actualizar';
                    document.querySelector("#name-field").value = this.dataset.name || '';
                    document.querySelector("#code-field").value = this.dataset.code || '';
                    document.querySelector("#registered-citizens-field").value = this.dataset.registeredCitizens || '';
                    document.querySelector("#address-field").value = this.dataset.address || '';
                    document.querySelector("#total-computed-records-field").value = this.dataset.totalComputedRecords || 0;
                    document.querySelector("#total-annulled-records-field").value = this.dataset.totalAnnulledRecords || 0;
                    document.querySelector("#total-enabled-records-field").value = this.dataset.totalEnabledRecords || 0;
                    document.querySelector("#active-field").checked = this.dataset.active === '1';
                    const deptSelect = document.querySelector("#department-field");
                    if (this.dataset.departmentId) {
                        deptSelect.value = this.dataset.departmentId;
                        deptSelect.dispatchEvent(new Event("change"));
                        setTimeout(async () => {
                            if (this.dataset.provinceId) {
                                await selectAndTrigger("#province-field", this.dataset.provinceId);
                                await selectAndTrigger("#municipality-field", this.dataset.municipalityId);
                                await selectAndTrigger("#locality-field", this.dataset.localityId);
                                if (this.dataset.districtId) {
                                    await selectAndTrigger("#district-field", this.dataset.districtId);
                                }
                                document.querySelector("#zone-field").value = this.dataset.zoneId || '';
                            }
                        }, 500);
                    }
                    clearValidationErrors();
                });
            });
            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deleteUrl = this.getAttribute('data-delete-url');
                    document.getElementById('deleteForm').action = deleteUrl;
                });
            });            
            const form = document.getElementById('institutionForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;                
                const nameField = document.getElementById('name-field');
                if (!nameField.value.trim()) {
                    nameField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    nameField.classList.remove('is-invalid');
                }                
                const localityField = document.getElementById('locality-field');
                if (!localityField.value) {
                    localityField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    localityField.classList.remove('is-invalid');
                }                
                const departmentField = document.getElementById('department-field');
                if (!departmentField.value) {
                    departmentField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    departmentField.classList.remove('is-invalid');
                }                
                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                    Swal.fire({
                        title: 'Campos requeridos',
                        text: 'Por favor complete todos los campos obligatorios.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
            document.getElementById('showModal').addEventListener('hidden.bs.modal', function () {
                clearValidationErrors();
            });

            async function selectAndTrigger(selector, value) {
                const select = document.querySelector(selector);
                if (value && select) {
                    select.value = value;
                    select.dispatchEvent(new Event("change"));
                    return new Promise(resolve => setTimeout(resolve, 200));
                }
            }
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });
            }
            setTimeout(function() {
                document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
        
        @if(session('import_errors'))
            const importErrorModal = new bootstrap.Modal(document.getElementById('importErrorModal'));
            importErrorModal.show();
        @endif
        function deleteMultiple() {
            const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);            
            if (ids.length === 0) {
                Swal.fire({
                    title: 'Sin selección',
                    text: 'Por favor seleccione al menos un registro para eliminar.',
                    icon: 'info',
                    confirmButtonText: 'Entendido'
                });
                return;
            }            
            Swal.fire({
                title: '¿Está seguro?',
                text: `¿Desea eliminar ${ids.length} registros seleccionados?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route("institutions.deleteMultiple") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Error: ${error}`
                        );
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value && result.value.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: result.value.message || `Se eliminaron ${ids.length} registros correctamente.`,
                            icon: 'success',
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            ids.forEach(id => {
                                const row = document.querySelector(`.child-checkbox[value="${id}"]`).closest('tr');
                                if (row) {
                                    row.remove();
                                }
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: result.value?.message || 'Ocurrió un error al eliminar los registros.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                }
            });
        }        
    </script>
@endsection