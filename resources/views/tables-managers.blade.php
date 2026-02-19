@extends('layouts.master')
@section('title')
    @lang('translation.list-managers')
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
            Gestores de Mesas
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Gestores de Mesas</h4>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="listjs-table" id="managerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                        id="create-btn" data-bs-target="#showModal"><i
                                            class="ri-add-line align-bottom me-1"></i> Agregar</button>
                                    <button class="btn btn-soft-danger" onClick="deleteMultiple()"><i
                                            class="ri-delete-bin-2-line"></i></button>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control search" placeholder="Buscar...">
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
                                        <th class="sort" data-sort="name">Nombre</th>
                                        <th class="sort" data-sort="id_card">Cédula</th>
                                        <th class="sort" data-sort="role">Rol</th>
                                        <th class="sort" data-sort="email">Email</th>
                                        <th class="sort" data-sort="voting_table">Mesa de Votación</th>
                                        <th class="sort" data-sort="institution">Institución</th>
                                        <th class="sort actions-column" data-sort="action">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @foreach($managers as $manager)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="{{ $manager->id }}">
                                                </div>
                                            </th>
                                            <td class="name">{{ $manager->name }}</td>
                                            <td class="id_card">{{ $manager->id_card ?? 'N/A' }}</td>
                                            <td class="role">
                                                @php
                                                    $roleClasses = [
                                                        'presidente' => 'primary',
                                                        'secretario' => 'info',
                                                        'escrutador' => 'secondary'
                                                    ];
                                                    $roleLabels = [
                                                        'presidente' => 'Presidente',
                                                        'secretario' => 'Secretario',
                                                        'escrutador' => 'Escrutador'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $roleClasses[$manager->role] }}-subtle text-{{ $roleClasses[$manager->role] }}">
                                                    {{ $roleLabels[$manager->role] }}
                                                </span>
                                            </td>
                                            <td class="email">{{ $manager->user->email ?? 'N/A' }}</td>
                                            <td class="voting_table">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $manager->votingTable->code ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="institution">
                                                {{ $manager->votingTable->institution->name ?? 'N/A' }}
                                                @if($manager->votingTable->institution->locality ?? false)
                                                    <br><small class="text-muted">{{ $manager->votingTable->institution->locality->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="{{ $manager->id }}"
                                                            data-name="{{ $manager->name }}"
                                                            data-id_card="{{ $manager->id_card }}"
                                                            data-role="{{ $manager->role }}"
                                                            data-voting_table_id="{{ $manager->voting_table_id }}"
                                                            data-institution_id="{{ $manager->votingTable->institution_id ?? '' }}"
                                                            data-email="{{ $manager->user->email ?? '' }}"
                                                            data-update-url="{{ route('managers.update', $manager->id) }}">
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="{{ $manager->id }}"
                                                            data-delete-url="{{ route('managers.destroy', $manager->id) }}">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            @if($managers->isEmpty())
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay gestores registrados en el sistema.</p>
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

    <!-- Modals -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Gestor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="managerForm" method="POST" class="tablelist-form" autocomplete="off">
                    @csrf
                    <input type="hidden" id="method_field" name="_method" value="">
                    <input type="hidden" id="manager_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name-field" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="name-field" name="name" class="form-control @error('name') is-invalid @enderror" 
                                placeholder="Ingrese el nombre completo" value="{{ old('name') }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_card-field" class="form-label">Número de Identificación</label>
                            <input type="text" id="id_card-field" name="id_card" class="form-control @error('id_card') is-invalid @enderror" 
                                placeholder="Ingrese el número de identificación" value="{{ old('id_card') }}" />
                            @error('id_card')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email-field" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email-field" name="email" class="form-control @error('email') is-invalid @enderror" 
                                placeholder="Ingrese el correo electrónico" value="{{ old('email') }}" required />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-field" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="password-field" name="password" class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Ingrese la contraseña" required />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor ingrese una contraseña.</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation-field" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation-field" name="password_confirmation" class="form-control" 
                                placeholder="Confirme la contraseña" required />
                        </div>

                        <div class="mb-3">
                            <label for="role-field" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror" name="role" id="role-field" required>
                                <option value="">Seleccione un rol</option>
                                <option value="presidente" {{ old('role') == 'presidente' ? 'selected' : '' }}>Presidente</option>
                                <option value="secretario" {{ old('role') == 'secretario' ? 'selected' : '' }}>Secretario</option>
                                <option value="escrutador" {{ old('role') == 'escrutador' ? 'selected' : '' }}>Escrutador</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor seleccione un rol.</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="institution-field" class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-control @error('institution_id') is-invalid @enderror" name="institution_id" id="institution-field" required>
                                <option value="">Seleccione una institución</option>
                                @foreach($institutions as $institution)
                                    <option value="{{ $institution->id }}" {{ old('institution_id') == $institution->id ? 'selected' : '' }}>
                                        {{ $institution->name }} 
                                        @if($institution->locality)
                                            - {{ $institution->locality->name }}
                                        @endif
                                        @if($institution->district)
                                            , {{ $institution->district->name }}
                                        @endif
                                        @if($institution->zone)
                                            , Zona {{ $institution->zone->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor seleccione una institución.</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="voting_table-field" class="form-label">Mesa de Votación <span class="text-danger">*</span></label>
                            <select class="form-control @error('voting_table_id') is-invalid @enderror" name="voting_table_id" id="voting_table-field" required>
                                <option value="">Primero seleccione una institución</option>
                            </select>
                            @error('voting_table_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Por favor seleccione una mesa de votación.</div>
                            @enderror
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
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar este gestor?</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn w-sm btn-danger">Sí, eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end modal -->
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const institutionSelect = new Choices('#institution-field', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
            });
            const votingTableSelect = new Choices('#voting_table-field', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
            });
            const roleSelect = new Choices('#role-field', {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false,
            });

            document.getElementById('institution-field').addEventListener('change', function() {
                const institutionId = this.value;
                const votingTableField = document.getElementById('voting_table-field');
                
                if (institutionId) {
                    fetch(`/managers/voting-tables/${institutionId}`)
                        .then(response => response.json())
                        .then(data => {
                            votingTableField.innerHTML = '<option value="">Seleccione una mesa de votación</option>';
                            
                            data.forEach(votingTable => {
                                const option = document.createElement('option');
                                option.value = votingTable.id;
                                option.textContent = `${votingTable.code} - Mesa ${votingTable.number}`;
                                votingTableField.appendChild(option);
                            });
                            
                            votingTableSelect.destroy();
                            votingTableSelect.init();
                        })
                        .catch(error => {
                            console.error('Error loading voting tables:', error);
                            votingTableField.innerHTML = '<option value="">Error al cargar mesas</option>';
                        });
                } else {
                    votingTableField.innerHTML = '<option value="">Primero seleccione una institución</option>';
                    votingTableSelect.destroy();
                    votingTableSelect.init();
                }
            });

            var options = {valueNames: ['name', 'id_card', 'role', 'email', 'voting_table', 'institution']};
            var managerList = new List('managerList', options).on('updated', function(list) {
                attachEditEventListeners();
                attachDeleteEventListeners();
            });

            document.getElementById('checkAll').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('input[name="chk_child"]');
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = this.checked;
                }
            });

            document.getElementById('create-btn').addEventListener('click', function() {
                document.getElementById('exampleModalLabel').textContent = 'Agregar Nuevo Gestor';
                document.getElementById('managerForm').action = "{{ route('managers.store') }}";
                document.getElementById('method_field').value = '';            
                document.getElementById('managerForm').reset();
                document.getElementById('manager_id').value = '';
                document.getElementById('password-field').required = true;
                document.getElementById('password_confirmation-field').required = true;
                document.getElementById('save-btn').textContent = 'Guardar';                
                institutionSelect.setChoiceByValue('');
                votingTableSelect.setChoiceByValue('');
                roleSelect.setChoiceByValue('presidente');
                
                clearValidationErrors();
            });

            function attachEditEventListeners() {
                document.querySelectorAll('.edit-item-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        const id_card = this.getAttribute('data-id_card');
                        const role = this.getAttribute('data-role');
                        const voting_table_id = this.getAttribute('data-voting_table_id');
                        const institution_id = this.getAttribute('data-institution_id');
                        const email = this.getAttribute('data-email');
                        const updateUrl = this.getAttribute('data-update-url');
                        
                        document.getElementById('exampleModalLabel').textContent = 'Editar Gestor';
                        document.getElementById('managerForm').action = updateUrl;
                        document.getElementById('method_field').value = 'PUT';
                        document.getElementById('manager_id').value = id;
                        document.getElementById('name-field').value = name;
                        document.getElementById('id_card-field').value = id_card;
                        document.getElementById('email-field').value = email;
                        document.getElementById('password-field').required = false;
                        document.getElementById('password_confirmation-field').required = false;
                        
                        institutionSelect.setChoiceByValue(institution_id);
                        roleSelect.setChoiceByValue(role);
                        
                        if (institution_id) {
                            fetch(`/managers/voting-tables/${institution_id}`)
                                .then(response => response.json())
                                .then(data => {
                                    const votingTableField = document.getElementById('voting_table-field');
                                    votingTableField.innerHTML = '<option value="">Seleccione una mesa de votación</option>';
                                    
                                    data.forEach(votingTable => {
                                        const option = document.createElement('option');
                                        option.value = votingTable.id;
                                        option.textContent = `${votingTable.code} - Mesa ${votingTable.number}`;
                                        if (votingTable.id == voting_table_id) {
                                            option.selected = true;
                                        }
                                        votingTableField.appendChild(option);
                                    });
                                    
                                    votingTableSelect.destroy();
                                    votingTableSelect.init();
                                    votingTableSelect.setChoiceByValue(voting_table_id);
                                });
                        }
                        
                        document.getElementById('save-btn').textContent = 'Actualizar';
                        clearValidationErrors();
                    });
                });
            }

            function attachDeleteEventListeners() {
                document.querySelectorAll('.remove-item-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const deleteUrl = this.getAttribute('data-delete-url');
                        document.getElementById('deleteForm').action = deleteUrl;
                    });
                });
            }

            attachEditEventListeners();
            attachDeleteEventListeners();

            const form = document.getElementById('managerForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                const requiredFields = [
                    'name', 'email', 'role', 'institution_id', 'voting_table_id'
                ];
                
                requiredFields.forEach(field => {
                    const element = document.getElementById(field + '-field');
                    if (!element.value.trim()) {
                        element.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        element.classList.remove('is-invalid');
                    }
                });
                
                if (!document.getElementById('manager_id').value) {
                    const password = document.getElementById('password-field');
                    const passwordConfirmation = document.getElementById('password_confirmation-field');
                    
                    if (!password.value) {
                        password.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (password.value !== passwordConfirmation.value) {
                        passwordConfirmation.classList.add('is-invalid');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
            
            document.getElementById('showModal').addEventListener('hidden.bs.modal', function () {
                clearValidationErrors();
            });
            
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });
            }
        });
        
        function deleteMultiple() {
            alert('Función de eliminar múltiple - por implementar');
        }
    </script>
@endsection