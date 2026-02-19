@extends('layouts.master')
@section('title')
    @lang('translation.list-voting-tables')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .datalist-container {
            position: relative;
        }
        .datalist-suggestions {
            position: absolute;
            border: 1px solid #d1d5db;
            border-top: none;
            z-index: 1000;
            width: 100%;
            background: white;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }
        .datalist-suggestion {
            padding: 8px 12px;
            cursor: pointer;
        }
        .datalist-suggestion:hover {
            background-color: #f3f4f6;
        }
        input[list]::-webkit-calendar-picker-indicator {
            display: none !important;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Tables
        @endslot
        @slot('title')
            Mesas de Votación
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Mesas de Votación</h4>
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
                    
                    <div class="listjs-table" id="votingTableList">
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
                                                <a class="dropdown-item" href="{{ route('voting-tables.export') }}">
                                                    <i class="ri-file-excel-line me-2"></i> Exportar Datos
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('voting-tables.template') }}">
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
                                        <input type="text" class="form-control search" placeholder="Buscar mesa...">
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
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th class="sort" data-sort="institution">Institución</th>
                                        <th class="sort code-column" data-sort="table_code">Código</th>
                                        <th class="sort" data-sort="table_number">Número</th>
                                        <th class="sort" data-sort="registered_citizens">Electores Habilitados</th>
                                        <th class="sort status-column" data-sort="status">Estado</th>
                                        <th class="sort actions-column" data-sort="action">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @foreach($votingTables as $table)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input child-checkbox" type="checkbox" name="chk_child" value="{{ $table->id }}">
                                                </div>
                                            </th>
                                            <td class="institution">{{ $table->institution->name ?? 'N/A' }}</td>
                                            <td class="table_code">
                                                <span class="badge bg-info-subtle text-info">{{ $table->code }}</span>
                                            </td>
                                            <td class="table_number">{{ $table->number }}</td>
                                            <td class="registered_citizens">{{ $table->registered_citizens }}</td>
                                            <td class="status">
                                                @php
                                                    $statusClasses = [
                                                        'activo' => 'success',
                                                        'cerrado' => 'danger',
                                                        'pendiente' => 'warning'
                                                    ];
                                                    $statusLabels = [
                                                        'activo' => 'Activo',
                                                        'cerrado' => 'Cerrada',
                                                        'pendiente' => 'Pendiente'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusClasses[$table->status] }}-subtle text-{{ $statusClasses[$table->status] }}">
                                                    {{ $statusLabels[$table->status] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="{{ $table->id }}"
                                                            data-code="{{ $table->code }}"
                                                            data-number="{{ $table->number }}"
                                                            data-from_name="{{ $table->from_name }}"
                                                            data-to_name="{{ $table->to_name }}"
                                                            data-registered_citizens="{{ $table->registered_citizens }}"
                                                            data-computed_records="{{ $table->computed_records }}"
                                                            data-annulled_records="{{ $table->annulled_records }}"
                                                            data-enabled_records="{{ $table->enabled_records }}"
                                                            data-status="{{ $table->status }}"
                                                            data-institution_id="{{ $table->institution_id }}"
                                                            data-update-url="{{ route('voting-tables.update', $table->id) }}">
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="{{ $table->id }}"
                                                            data-delete-url="{{ route('voting-tables.destroy', $table->id) }}">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            @if($votingTables->isEmpty())
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay mesas de votación registradas en el sistema.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <div class="pagination-wrap hstack gap-2">
                                @if($votingTables->previousPageUrl())
                                    <a class="page-item pagination-prev" href="{{ $votingTables->previousPageUrl() }}">
                                        Anterior
                                    </a>
                                @else
                                    <span class="page-item pagination-prev disabled">Anterior</span>
                                @endif
                                
                                <ul class="pagination mb-0">
                                    @foreach(range(1, $votingTables->lastPage()) as $page)
                                        <li class="page-item {{ $votingTables->currentPage() == $page ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $votingTables->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                @if($votingTables->nextPageUrl())
                                    <a class="page-item pagination-next" href="{{ $votingTables->nextPageUrl() }}">
                                        Siguiente
                                    </a>
                                @else
                                    <span class="page-item pagination-next disabled">Siguiente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Nueva Mesa de Votación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="votingTableForm" method="POST" class="tablelist-form" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" id="method_field" name="_method" value="POST">
                    <input type="hidden" id="voting_table_id" name="id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line align-middle me-1"></i> 
                            El código se generará automáticamente a partir del numero de mesa si no se especifica.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="number-field" class="form-label">Número de Mesa <span class="text-danger">*</span></label>
                                    <input type="number" id="number-field" name="number" class="form-control @error('number') is-invalid @enderror" 
                                        placeholder="Ingrese el número de mesa" value="{{ old('number') }}" min="1" required />
                                    @error('number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor ingrese un número válido.</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code-field" class="form-label">Código de Mesa</label>
                                    <input type="text" id="code-field" name="code" class="form-control @error('code') is-invalid @enderror" 
                                        placeholder="Ingrese el código de mesa (ej: MESA-001)" value="{{ old('code') }}" />
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_name-field" class="form-label">Desde (Nombre)</label>
                                    <input type="text" id="from_name-field" name="from_name" class="form-control @error('from_name') is-invalid @enderror" 
                                        placeholder="Ej: Juan" value="{{ old('from_name') }}" />
                                    @error('from_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_name-field" class="form-label">Hasta (Nombre)</label>
                                    <input type="text" id="to_name-field" name="to_name" class="form-control @error('to_name') is-invalid @enderror" 
                                        placeholder="Ej: Pedro" value="{{ old('to_name') }}" />
                                    @error('to_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="institution_id-field" class="form-label">Institución/Recinto<span class="text-danger">*</span></label>
                                    <select class="form-control @error('institution_id') is-invalid @enderror" name="institution_id" id="institution_id-field" required>
                                        <option value="">Seleccione Institución</option>
                                        @foreach($institutions as $institution)
                                            <option value="{{ $institution->id }}" {{ old('institution_id') == $institution->id ? 'selected' : '' }}>
                                                {{ $institution->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('institution_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione una institución válida.</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="registered_citizens-field" class="form-label">Ciudadanos Registrados</label>
                                    <input type="number" id="registered_citizens-field" name="registered_citizens" class="form-control @error('registered_citizens') is-invalid @enderror" 
                                        placeholder="Ingrese cantidad de ciudadanos" value="{{ old('registered_citizens', 0) }}" min="0" />
                                    @error('registered_citizens')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="computed_records-field" class="form-label">Papeletas en Anfora</label>
                                    <input type="number" id="computed_records-field" name="computed_records" class="form-control @error('computed_records') is-invalid @enderror" 
                                        value="{{ old('computed_records', 0) }}" min="0" />
                                    @error('computed_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="annulled_records-field" class="form-label">Papeletas Anuladas</label>
                                    <input type="number" id="annulled_records-field" name="annulled_records" class="form-control @error('annulled_records') is-invalid @enderror" 
                                        value="{{ old('annulled_records', 0) }}" min="0" />
                                    @error('annulled_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="enabled_records-field" class="form-label">Papeletas Habilitadas</label>
                                    <input type="number" id="enabled_records-field" name="enabled_records" class="form-control @error('enabled_records') is-invalid @enderror" 
                                        value="{{ old('enabled_records', 0) }}" min="0" />
                                    @error('enabled_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status-field" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" name="status" id="status-field" required>
                                        <option value="">Seleccione un estado</option>
                                        <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="cerrado" {{ old('status') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="invalid-feedback">Por favor seleccione un estado.</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="save-btn">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar el Registro?</p>
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
                <form method="POST" action="{{ route('voting-tables.import') }}" enctype="multipart/form-data">
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
                            <div class="form-text">Archivos permitidos: .xlsx, .xls, .csv (máx. 5MB)</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            <strong>Importante:</strong> Asegúrese de que el archivo cumpla con la estructura de la plantilla.
                            <br>
                            <a href="{{ route('voting-tables.template') }}" class="alert-link">
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
                    <a href="{{ route('voting-tables.template') }}" class="btn btn-info">
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
        document.addEventListener('DOMContentLoaded', function() {
            const institutionSelect = new Choices('#institution_id-field', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
            });
            var options = {
                valueNames: ['table_code', 'table_number', 'from_name', 'to_name', 'institution', 'status'],
                page: 10,
                pagination: true
            };
            var votingTableList = new List('votingTableList', options);
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

            document.getElementById('create-btn').addEventListener('click', function() {
                document.getElementById('exampleModalLabel').textContent = 'Agregar Mesa de Votación';
                document.getElementById('votingTableForm').action = "{{ route('voting-tables.store') }}";
                document.getElementById('method_field').value = 'POST';            
                document.getElementById('votingTableForm').reset();
                document.getElementById('voting_table_id').value = '';
                document.getElementById('status-field').value = 'pending';
                document.getElementById('save-btn').innerHTML = '<i class="ri-save-line me-1"></i>Guardar';
                clearValidationErrors();
            });
            document.querySelectorAll('.edit-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const code = this.getAttribute('data-code');
                    const number = this.getAttribute('data-number');
                    const from_name = this.getAttribute('data-from_name');
                    const to_name = this.getAttribute('data-to_name');
                    const registered_citizens = this.getAttribute('data-registered_citizens');
                    const computed_records = this.getAttribute('data-computed_records');
                    const annulled_records = this.getAttribute('data-annulled_records');
                    const enabled_records = this.getAttribute('data-enabled_records');
                    const status = this.getAttribute('data-status');
                    const institution_id = this.getAttribute('data-institution_id');
                    const updateUrl = this.getAttribute('data-update-url');
                    document.getElementById('exampleModalLabel').textContent = 'Editar Mesa de Votación';
                    document.getElementById('votingTableForm').action = updateUrl;
                    document.getElementById('method_field').value = 'PUT';
                    document.getElementById('voting_table_id').value = id;
                    document.getElementById('code-field').value = code;
                    document.getElementById('number-field').value = number;
                    document.getElementById('from_name-field').value = from_name;
                    document.getElementById('to_name-field').value = to_name;
                    document.getElementById('registered_citizens-field').value = registered_citizens;
                    document.getElementById('computed_records-field').value = computed_records;
                    document.getElementById('annulled_records-field').value = annulled_records;
                    document.getElementById('enabled_records-field').value = enabled_records;
                    document.getElementById('institution_id-field').value = institution_id;
                    document.getElementById('status-field').value = status;
                    document.getElementById('save-btn').innerHTML = '<i class="ri-refresh-line me-1"></i>Actualizar';
                    clearValidationErrors();
                });
            });
            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deleteUrl = this.getAttribute('data-delete-url');
                    document.getElementById('deleteForm').action = deleteUrl;
                });
            });
            const form = document.getElementById('votingTableForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;                
                const numberField = document.getElementById('number-field');
                if (!numberField.value.trim() || parseInt(numberField.value) < 1) {
                    numberField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    numberField.classList.remove('is-invalid');
                }  
                const institutionIdField = document.getElementById('institution_id-field');
                if (!institutionIdField.value) {
                    institutionIdField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    institutionIdField.classList.remove('is-invalid');
                }                
                const statusField = document.getElementById('status-field');
                if (!statusField.value) {
                    statusField.classList.add('is-invalid');
                    isValid = false;
                } else {
                    statusField.classList.remove('is-invalid');
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
                    return fetch('{{ route("voting-tables.deleteMultiple") }}', {
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