
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-candidates'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .image-preview-container {
            margin-top: 10px;
            text-align: center;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            margin: 5px;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            border: 1px solid #dee2e6;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Tables
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Candidatos
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Candidatos</h4>
                </div>
                
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>
                    
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="listjs-table" id="candidateList">
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
                                        <th class="sort" data-sort="photo">Foto</th>
                                        <th class="sort" data-sort="name">Nombre</th>
                                        <th class="sort" data-sort="party">Partido</th>
                                        <th class="sort" data-sort="color">Color</th>
                                        <th class="sort" data-sort="election_type">Tipo de Elección</th>
                                        <th class="sort" data-sort="type">Tipo</th>
                                        <th class="actions-column">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="<?php echo e($candidate->id); ?>">
                                                </div>
                                            </th>
                                            <td class="photo">
                                                <?php if($candidate->photo): ?>
                                                    <img src="<?php echo e($candidate->photo_url); ?>" alt="<?php echo e($candidate->name); ?>" class="avatar-xs rounded-circle">
                                                <?php else: ?>
                                                    <div class="avatar-xs bg-light rounded-circle"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="name"><?php echo e($candidate->name); ?></td>
                                            <td class="party">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <?php if($candidate->party_logo): ?>
                                                        <img src="<?php echo e($candidate->party_logo_url); ?>" alt="<?php echo e($candidate->party); ?>" class="avatar-xs rounded-circle">
                                                    <?php endif; ?>
                                                    <div>
                                                        <?php echo e($candidate->party); ?>

                                                        <?php if($candidate->party_full_name): ?>
                                                            <br><small class="text-muted"><?php echo e($candidate->party_full_name); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="color">
                                                <?php if($candidate->color): ?>
                                                    <div class="color-preview" style="background-color: <?php echo e($candidate->color); ?>"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="election_type">
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <?php echo e($candidate->electionType->name ?? 'N/A'); ?>

                                                </span>
                                            </td>
                                            <td class="type">
                                                <span class="badge 
                                                    <?php if($candidate->type === 'candidato'): ?> bg-success
                                                    <?php elseif($candidate->type === 'blank_votes'): ?> bg-warning
                                                    <?php elseif($candidate->type === 'null_votes'): ?> bg-danger
                                                    <?php else: ?> bg-secondary <?php endif; ?>">
                                                    <?php echo e($candidate->type === 'candidato' ? 'Candidato' : 
                                                       ($candidate->type === 'blank_votes' ? 'Votos en Blanco' : 
                                                       ($candidate->type === 'null_votes' ? 'Votos Nulos' : $candidate->type))); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="<?php echo e($candidate->id); ?>"
                                                            data-name="<?php echo e($candidate->name); ?>"
                                                            data-party="<?php echo e($candidate->party); ?>"
                                                            data-party_full_name="<?php echo e($candidate->party_full_name); ?>"
                                                            data-color="<?php echo e($candidate->color); ?>"
                                                            data-election_type_id="<?php echo e($candidate->election_type_id); ?>"
                                                            data-type="<?php echo e($candidate->type); ?>"
                                                            data-photo="<?php echo e($candidate->photo); ?>"
                                                            data-party_logo="<?php echo e($candidate->party_logo); ?>"
                                                            data-photo-url="<?php echo e($candidate->photo_url); ?>"
                                                            data-party-logo-url="<?php echo e($candidate->party_logo_url); ?>"
                                                            data-update-url="<?php echo e(route('candidates.update', $candidate->id)); ?>">
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="<?php echo e($candidate->id); ?>"
                                                            data-delete-url="<?php echo e(route('candidates.destroy', $candidate->id)); ?>">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            
                            <?php if($candidates->isEmpty()): ?>
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay candidatos registrados en el sistema.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Candidato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="candidateForm" method="POST" class="tablelist-form" autocomplete="off" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="method_field" name="_method" value="">
                    <input type="hidden" id="candidate_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name-field" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" id="name-field" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        placeholder="Ingrese el nombre completo" value="<?php echo e(old('name')); ?>" required />
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="party-field" class="form-label">Partido Político <span class="text-danger">*</span></label>
                                    <input type="text" id="party-field" name="party" class="form-control <?php $__errorArgs = ['party'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        placeholder="Ingrese el partido político" value="<?php echo e(old('party')); ?>" required />
                                    <?php $__errorArgs = ['party'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Por favor ingrese un partido político.</div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="party_full_name-field" class="form-label">Nombre Completo del Partido</label>
                                    <input type="text" id="party_full_name-field" name="party_full_name" class="form-control <?php $__errorArgs = ['party_full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        placeholder="Ingrese el nombre completo del partido" value="<?php echo e(old('party_full_name')); ?>" />
                                    <?php $__errorArgs = ['party_full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color-field" class="form-label">Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color w-100 <?php $__errorArgs = ['color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="color-field" name="color" value="<?php echo e(old('color', '#1b8af8')); ?>" required>
                                    <?php $__errorArgs = ['color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Por favor seleccione un color.</div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="election_type_id-field" class="form-label">Tipo de Elección <span class="text-danger">*</span></label>
                                    <select class="form-control <?php $__errorArgs = ['election_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="election_type_id" id="election_type_id-field" required>
                                        <option value="">Seleccione un tipo de elección</option>
                                        <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $electionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($electionType->id); ?>" <?php echo e(old('election_type_id') == $electionType->id ? 'selected' : ''); ?>>
                                                <?php echo e($electionType->name); ?> (<?php echo e($electionType->type); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['election_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Por favor seleccione un tipo de elección.</div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type-field" class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-control <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="type" id="type-field" required>
                                        <option value="">Seleccione un tipo</option>
                                        <option value="candidato" <?php echo e(old('type') == 'candidato' ? 'selected' : ''); ?>>Candidato</option>
                                        <option value="blank_votes" <?php echo e(old('type') == 'blank_votes' ? 'selected' : ''); ?>>Votos en Blanco</option>
                                        <option value="null_votes" <?php echo e(old('type') == 'null_votes' ? 'selected' : ''); ?>>Votos Nulos</option>
                                    </select>
                                    <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Por favor seleccione un tipo.</div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="photo-field" class="form-label">Foto del Candidato</label>
                                    <input type="file" id="photo-field" name="photo" class="form-control <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        accept="image/*" />
                                    <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="image-preview-container" id="photo-preview-container">
                                        <img id="photo-preview" class="image-preview" src="" style="display: none;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="party_logo-field" class="form-label">Logo del Partido</label>
                                    <input type="file" id="party_logo-field" name="party_logo" class="form-control <?php $__errorArgs = ['party_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        accept="image/*" />
                                    <?php $__errorArgs = ['party_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="image-preview-container" id="party-logo-preview-container">
                                        <img id="party-logo-preview" class="image-preview" src="" style="display: none;">
                                    </div>
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
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar este candidato?</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn w-sm btn-danger">Sí, eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end modal -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const electionTypeSelect = new Choices('#election_type_id-field', {
                searchEnabled: true,
                shouldSort: false,
                placeholder: true
            });
            const typeSelect = new Choices('#type-field', {
                searchEnabled: false,
                shouldSort: false,
                placeholder: true
            });

            document.getElementById('photo-field').addEventListener('change', function(e) {
                const preview = document.getElementById('photo-preview');
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
            document.getElementById('party_logo-field').addEventListener('change', function(e) {
                const preview = document.getElementById('party-logo-preview');
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            document.querySelectorAll('.edit-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = document.getElementById('showModal');
                    const form = document.getElementById('candidateForm');
                    const modalTitle = modal.querySelector('.modal-title');
                    const methodField = document.getElementById('method_field');
                    const candidateId = document.getElementById('candidate_id');
                    
                    modalTitle.textContent = 'Editar Candidato';
                    form.action = this.dataset.updateUrl;
                    methodField.value = 'PUT';
                    candidateId.value = this.dataset.id;
                    document.getElementById('name-field').value = this.dataset.name;
                    document.getElementById('party-field').value = this.dataset.party;
                    document.getElementById('party_full_name-field').value = this.dataset.party_full_name || '';
                    document.getElementById('color-field').value = this.dataset.color;
                    document.getElementById('election_type_id-field').value = this.dataset.election_type_id;
                    document.getElementById('type-field').value = this.dataset.type;
                    
                    const photoPreview = document.getElementById('photo-preview');
                    const partyLogoPreview = document.getElementById('party-logo-preview');
                    
                    if (this.dataset.photoUrl) {
                        photoPreview.src = this.dataset.photoUrl;
                        photoPreview.style.display = 'block';
                    } else {
                        photoPreview.style.display = 'none';
                    }
                    
                    if (this.dataset.partyLogoUrl) {
                        partyLogoPreview.src = this.dataset.partyLogoUrl;
                        partyLogoPreview.style.display = 'block';
                    } else {
                        partyLogoPreview.style.display = 'none';
                    }
                });
            });

            document.getElementById('create-btn').addEventListener('click', function() {
                const modal = document.getElementById('showModal');
                const form = document.getElementById('candidateForm');
                const modalTitle = modal.querySelector('.modal-title');
                const methodField = document.getElementById('method_field');
                const candidateId = document.getElementById('candidate_id');
                
                modalTitle.textContent = 'Agregar Nuevo Candidato';
                form.action = "<?php echo e(route('candidates.store')); ?>";
                methodField.value = '';
                candidateId.value = '';
                form.reset();
                document.getElementById('photo-preview').style.display = 'none';
                document.getElementById('party-logo-preview').style.display = 'none';
            });

            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = this.dataset.deleteUrl;
                });
            });

        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/tables-candidates.blade.php ENDPATH**/ ?>