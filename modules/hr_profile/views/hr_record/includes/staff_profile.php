<?php
echo '<script src="' . module_dir_url('icash_tools', 'assets/js/staff_custom_functions.js?ver=12.7.13') . '"></script>';
?>
<style>
	.send_file {
		float: right;
	}

	.bg-danger, .bg-success {
		/* color: red; */
		/* display: block; */
		text-align: center;
		min-width: 100px;
		font-weight: bolder;
		padding: 2px 15px;
		border-radius: 15px;
	}
</style>
<div class="row">
	<?php
	if (isset($_GET['debug'])) {
		echo "modules\hr_profile\views\hr_record\includes\staff_profile.php";
	}
	if ($member->active == 0) { ?>
		<div class="alert alert-danger text-center"><?php echo _l('staff_profile_inactive_account'); ?></div>
		<hr />
	<?php } ?>
	<div class="col-md-12 pl-0">
		<div class="col-md-5">
			<div class="row">
				<div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
					<div class="card box-shadow-0 overflow-hidden">
						<?php if ($member->status_work == 'working') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_working"><?php echo _l('hr_working'); ?></span></div>
						<?php } elseif ($member->status_work == 'maternity_leave') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_maternity_leave"><?php echo _l('hr_maternity_leave'); ?></span></div>
						<?php } elseif ($member->status_work == 'inactivity') { ?>
							<div class="ribbon ribbon-top-right text-info"><span class="bg_inactivity"><?php echo _l('hr_inactivity'); ?></span></div>
						<?php } ?>
						<div class="card-body">
							<div class="text-center">
								<div class="userprofile">
									<div class="userpic  brround mb-3">
										<?php echo staff_profile_image($member->staffid, array('staff-profile-image-thumb'), 'thumb'); ?>
									</div>
									<h3 class="username mb-2"><?php echo html_entity_decode($member->firstname . ' ' . $member->lastname); ?></h3>
									<div class="socials text-center mt-3">
										<!-- <a href="facebook: <?php echo html_escape($member->facebook); ?>" class="btn btn-circle">
											<i class="fa fa-facebook"></i>
										</a>
										<a href="linkedin: <?php echo html_escape($member->linkedin); ?>" class="btn btn-circle">
											<i class="fa fa-linkedin"></i>
										</a>
										<a href="skype: <?php echo html_escape($member->skype); ?>" class="btn btn-circle">
											<i class="fa fa-skype"></i>
										</a> -->
										<a href="mailto: <?php echo html_escape($member->email); ?>" class="btn btn-circle">
											<i class="fa fa-envelope"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
						<br>
					</div>
				</div>
			</div>
			<div class="card panel-theme">
				<div class="card-body no-padding">
					<ul class="list-group no-margin">
						<li class="list-group-item"><i class="fa fa-envelope mr-4"></i> <?php echo html_entity_decode($member->email) ?></li>
						<li class="list-group-item"><i class="fa fa-phone mr-4"></i> <?php echo html_entity_decode($member->phonenumber) ?></li>
					</ul>
				</div>

				<!-- <div class="card-header">
					<div class="float-left">
						<br>
						<h4 class="card-title text-center"><?php echo _l('staff_profile_departments') ?></h4>
					</div>
					<div class="clearfix"></div>
				</div> -->

				<div class="card-body no-padding">
					<ul class="list-group no-margin">
						<li class="list-group-item">

							<?php if (count($staff_departments) > 0) {
							?>
								<div class="form-group mtop10">
									<div class="clearfix"></div>
									<?php
									foreach ($departments as $department) {
									?>
										<?php
										foreach ($staff_departments as $staff_department) {
											if ($staff_department['departmentid'] == $department['departmentid']) { ?>
												<div class="chip-circle"><?php echo html_entity_decode($staff_department['name']); ?></div>
										<?php }
										}
										?>
									<?php } ?>
								</div>
							<?php } ?>

						</li>
					</ul>
				</div>

				<div class="card-header">
					<div class="float-left">
						<br>
						<h5 class="card-title text-left"><?php echo _l('hr_team_manage') . ':  ' ?></h5>
						<h4 class="card-title text-left"><?php echo staff_profile_image($member->team_manage, ['staff-profile-image-small']) . '  ' . get_staff_full_name($member->team_manage) ?></h4>
					</div>
					<div class="clearfix"></div>
				</div>

			</div>

		</div>
		<div class="col-md-7">

			<div class="col-md-12">
				<h4 class="bold"><?php echo _l('hr_general_infor'); ?></h4>

				<table class="table border table-striped ">
					<tbody>
						<!-- <tr class="project-overview">
							<td class="bold" width="40%"><?php echo _l('hr_hr_code'); ?></td>
							<td><?php echo html_entity_decode($member->staff_identifi); ?></td>
						</tr> -->
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('Razão Social'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_razao_social);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('Nome Fantasia'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_nome_fantasia);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('CNPJ'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_cnpj);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('Endereço'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_endereco_empresa) . ", " . html_entity_decode($custom_fields->staff_numero_empresa);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('Cidade'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_cidade . " - " . $custom_fields->staff_uf);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="30%"><?php echo _l('Bairro'); ?></td>
							<td><?php
								echo html_entity_decode($custom_fields->staff_bairro_empresa);
								?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo _l('hr_sex'); ?></td>
							<td><?php echo _l($member->sex); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo _l('hr_hr_birthday'); ?></td>
							<td><?php echo _d($member->birthday); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo _l('staff_add_edit_phonenumber'); ?></td>
							<td><?php echo html_entity_decode($member->phonenumber); ?></td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo _l('hr_status_label'); ?></td>
							<td>
								<?php echo html_entity_decode(_l($member->status_work)) ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold" width="40%"><?php echo _l('hr_hr_job_position'); ?></td>
							<td>
								<?php
								if ($member->job_position > 0) {
									$job_position_name = html_entity_decode(hr_profile_get_job_position_name($member->job_position))
								?>
									<a href="<?php echo admin_url() . 'hr_profile/job_position_view_edit/' . $member->job_position; ?>"><?php echo $job_position_name; ?></a>
								<?php
								}

								?>
							</td>
						</tr>


						<tr class="project-overview">
							<td class="bold"><?php echo _l('hr_hr_marital_status'); ?></td>
							<td><?php echo _l($custom_fields->staff_estado_civil); ?></td>
						</tr>

					</tbody>
				</table>
			</div>


			<div class="col-md-12">
				<h4><?php echo "Dados Bancários"; ?></h4>
				<table class="table border table-striped ">
					<tbody>
						<tr class="project-overview">
							<td class="bold"><?php echo "Tipo de Chave PIX"; ?></td>
							<td>
								<?php echo html_entity_decode($custom_fields->staff_tipo_de_chave); ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo "Chave PIX"; ?></td>
							<td>
								<?php echo html_entity_decode($custom_fields->staff_pix); ?>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo "Banco"; ?></td>
							<td>
								<?php echo html_entity_decode($custom_fields->staff_banco); ?>
							</td>
						</tr>

					</tbody>
				</table>
			</div>

			<div class="col-md-12">
				<h4><?php echo "Documentos"; ?></h4>
				<table class="table border table-striped ">
					<tbody>
						<tr class="project-overview">
							<td class="bold"><?php echo "Contrato Social"; ?></td>
							<td>
								<?php
								if ($member->contrato_social) {
									echo "<a href='" . html_entity_decode($member->contrato_social) . "' target='_NEW'><span class='bg-success'><i class=\"fa-solid fa-eye\"></i></span></a>";
								} else {
									echo "<span class=\"bg-danger\">Não enviado</span>";
								}
								?>
								<button type="button" class="btn btn-primary send_file" data-type="contrato_social" data-toggle="modal" data-target="#uploadFileModal">
									<i class="fa-solid fa-cloud-arrow-up"></i>
								</button>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo "Comp. Endereço"; ?></td>
							<td>
								<?php
								if ($member->comp_endereco) {
									echo "<a href='" . html_entity_decode($member->comp_endereco) . "' target='_NEW'><span class='bg-success'><i class=\"fa-solid fa-eye\"></i></span></a>";
								} else {
									echo "<span class=\"bg-danger\"><i class=\"fa-solid fa-eye-slash\"></i></span>";
								}
								?>
								<button type="button" class="btn btn-primary send_file" data-type="comp_endereco" data-toggle="modal" data-target="#uploadFileModal">
									<i class="fa-solid fa-cloud-arrow-up"></i>
								</button>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo "Foto Fachada"; ?></td>
							<td>
								<?php
								if ($member->foto_fachada) {
									echo "<a href='" . html_entity_decode($member->foto_fachada) . "' target='_NEW'><span class='bg-success'><i class=\"fa-solid fa-eye\"></i></span></a>";
								} else {
									echo "<span class=\"bg-danger\">Não enviado</span>";
								}
								?>
								<button type="button" class="btn btn-primary send_file" data-type="foto_fachada" data-toggle="modal" data-target="#uploadFileModal">
									<i class="fa-solid fa-cloud-arrow-up"></i>
								</button>
							</td>
						</tr>
						<tr class="project-overview">
							<td class="bold"><?php echo "Doc. Sócio"; ?></td>
							<td>
								<?php
								if ($member->doc_socio_principal) {
									echo "<a href='" . html_entity_decode($member->doc_socio_principal) . "' target='_NEW'><span class='bg-success'><i class=\"fa-solid fa-eye\"></i></span></a>";
								} else {
									echo "<span class=\"bg-danger\">Não enviado</span>";
								}
								?>
								<button type="button" class="btn btn-primary send_file" data-type="doc_socio_principal" data-toggle="modal" data-target="#uploadFileModal">
									<i class="fa-solid fa-cloud-arrow-up"></i>
								</button>

							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<!-- Modal HTML -->
	<div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="uploadFileModalLabel">Enviar <span id="type_file"></span></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="uploadFileForm" method="POST" enctype="multipart/form-data" action="<?= admin_url('icash_tools/upload_file') ?>">
					<?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
					<div class="modal-body">
						<div class="form-group">
							<label for="file">Escolha o arquivo</label>
							<input type="file" class="form-control" id="file" name="file" required>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="staffid" value="<?php echo $member->staffid; ?>">
						<input type="hidden" id="doc_type" name="doc_type">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
						<button type="submit" class="btn btn-primary">Enviar</button>
					</div>
				</form>

			</div>
		</div>
	</div>
	<?php

	if (isset($_GET['debug'])) {
		echo  realpath(ICASH_TOOLS_CLIENTS_UPLOADS);
		echo "<pre>";
		var_dump($custom_fields);
		var_dump($member);
		echo "</pre>";
	}
	?>
</div>