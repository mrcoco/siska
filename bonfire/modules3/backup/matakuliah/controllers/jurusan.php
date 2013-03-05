<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class jurusan extends Admin_Controller {

	//--------------------------------------------------------------------


	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict('Matakuliah.Jurusan.View');
		$this->load->model('matakuliah_model', null, true);
		$this->lang->load('matakuliah');
		
		Template::set_block('sub_nav', 'jurusan/_sub_nav');
	}

	//--------------------------------------------------------------------



	/*
		Method: index()

		Displays a list of form data.
	*/
	public function index()
	{

		// Deleting anything?
		if (isset($_POST['delete']))
		{
			$checked = $this->input->post('checked');

			if (is_array($checked) && count($checked))
			{
				$result = FALSE;
				foreach ($checked as $pid)
				{
					$result = $this->matakuliah_model->delete($pid);
				}

				if ($result)
				{
					Template::set_message(count($checked) .' '. lang('matakuliah_delete_success'), 'success');
				}
				else
				{
					Template::set_message(lang('matakuliah_delete_failure') . $this->matakuliah_model->error, 'error');
				}
			}
		}

		$data['records'] = $this->matakuliah_model->find_all();
        $data['data'] = $this->matakuliah_model->get_id_max();
        Template::set('data', $data);
		
		Template::set('toolbar_title', 'Manage matakuliah');
		Template::render();
	}

	//--------------------------------------------------------------------



	/*
		Method: create()

		Creates a matakuliah object.
	*/
	public function create()
	{
		$this->auth->restrict('Matakuliah.Jurusan.Create');

		if ($this->input->post('save'))
		{
			if ($insert_id = $this->save_matakuliah())
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('matakuliah_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'matakuliah');

				Template::set_message(lang('matakuliah_create_success'), 'success');
				//Template::redirect(SITE_AREA .'/jurusan/matakuliah');
			}
			else
			{
				Template::set_message(lang('matakuliah_create_failure') . $this->matakuliah_model->error, 'error');
			}
		}
		Assets::add_module_js('matakuliah', 'matakuliah.js');

		Template::set('toolbar_title', lang('matakuliah_create') . ' matakuliah');
		Template::render();
	}

	//--------------------------------------------------------------------



	/*
		Method: edit()

		Allows editing of matakuliah data.
	*/
	public function edit()
	{
		$id = $this->uri->segment(5);

		if (empty($id))
		{
			Template::set_message(lang('matakuliah_invalid_id'), 'error');
			redirect(SITE_AREA .'/jurusan/matakuliah');
		}

		if (isset($_POST['save']))
		{
			$this->auth->restrict('Matakuliah.Jurusan.Edit');

			if ($this->save_matakuliah('update', $id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('matakuliah_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'matakuliah');

				Template::set_message(lang('matakuliah_edit_success'), 'success');
			}
			else
			{
				Template::set_message(lang('matakuliah_edit_failure') . $this->matakuliah_model->error, 'error');
			}
		}
		else if (isset($_POST['delete']))
		{
			$this->auth->restrict('Matakuliah.Jurusan.Delete');

			if ($this->matakuliah_model->delete($id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('matakuliah_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'matakuliah');

				Template::set_message(lang('matakuliah_delete_success'), 'success');

				redirect(SITE_AREA .'/jurusan/matakuliah');
			} else
			{
				Template::set_message(lang('matakuliah_delete_failure') . $this->matakuliah_model->error, 'error');
			}
		}
		Template::set('matakuliah', $this->matakuliah_model->find_by('id',$id));
		Assets::add_module_js('matakuliah', 'matakuliah.js');

		Template::set('toolbar_title', lang('matakuliah_edit') . ' matakuliah');
		Template::render();
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------

	/*
		Method: save_matakuliah()

		Does the actual validation and saving of form data.

		Parameters:
			$type	- Either "insert" or "update"
			$id		- The ID of the record to update. Not needed for inserts.

		Returns:
			An INT id for successful inserts. If updating, returns TRUE on success.
			Otherwise, returns FALSE.
	*/
	private function save_matakuliah($type='insert', $id=0)
	{
		if ($type == 'update') {
			$_POST['id'] = $id;
		}

		
		//$this->form_validation->set_rules('matakuliah_kode_matakuliah','Kode Matakuliah','required|unique[matakuliah.kode_matakuliah,matakuliah.id]|max_length[10]');
		$this->form_validation->set_rules('matakuliah_nama_matakuliah','Nama Matakuliah','required|max_length[75]');
        $this->form_validation->set_rules('matakuliah_prodi','Program Studi','required|max_length[1]');
        $this->form_validation->set_rules('matakuliah_jenjang','Jenjang','required|max_length[1]');
        $this->form_validation->set_rules('matakuliah_kelompok','Kelompok Mata Kuliah','required|max_length[2]');
        $this->form_validation->set_rules('matakuliah_semester','Semester','required|max_length[1]');
        $this->form_validation->set_rules('matakuliah_k1','Kopetensi');
        $this->form_validation->set_rules('matakuliah_k3','Kopetensi');
		$this->form_validation->set_rules('matakuliah_sks_teori','Sks Teori','max_length[1]');
		$this->form_validation->set_rules('matakuliah_sks_praktek','Sks Praktek','max_length[1]');
		$this->form_validation->set_rules('matakuliah_sks_praktikum','Sks Praktikum','max_length[1]');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}

		// make sure we only pass in the fields we want
		
        $prodi = $this->input->post('matakuliah_prodi');
        $jenjang = $this->input->post('matakuliah_jenjang');
        $kelompok = $this->input->post('matakuliah_kelompok');
        
        // cek sks
        $sks_teori        = $this->input->post('matakuliah_sks_teori');
		$sks_praktek        = $this->input->post('matakuliah_sks_praktek');
		$sks_praktikum        = $this->input->post('matakuliah_sks_praktikum');
        $t_sks= $sks_teori+$sks_praktek+$sks_praktikum;        
        $semester = $this->input->post('matakuliah_semester');
        ($jenjang=='S') ?  $kopetensi = $this->input->post('matakuliah_k1'):$kopetensi = $this->input->post('matakuliah_k3');
        $kode_mata_kuliah=$prodi.$jenjang.$kelompok.$t_sks.$semester.$kopetensi;
        
        // cek id max dari data yang sudah dinputkan inputan
        $data = $this->matakuliah_model->get_id_max($kode_mata_kuliah);
        foreach ($data as $row){
            $kode=$row->id;
        }
        if($kode!='')
        {
           $kode = substr($kode,9,2)+1; 
           if($kode < 10) 
           {
                $kode = '0'.$kode;
           }
        } else 
        {
            $kode='01';
        }
                   
        $kode_mata_kuliah .= $kode; 
        $cek=$this->matakuliah_model->is_unique('kode_matakuliah',$kode_mata_kuliah);             
		$data = array();
		$data['kode_matakuliah']        = $kode_mata_kuliah;
		$data['nama_matakuliah']        = $this->input->post('matakuliah_nama_matakuliah');
		$data['sks_teori']              = $this->input->post('matakuliah_sks_teori');
		$data['sks_praktek']            = $this->input->post('matakuliah_sks_praktek');
		$data['sks_praktikum']          = $this->input->post('matakuliah_sks_praktikum');

		if ($type == 'insert' and $cek==TRUE)
		{
			$id = $this->matakuliah_model->insert($data);

			if (is_numeric($id))
			{
				$return = $id;
			} else
			{
				$return = FALSE;
			}
		}
		else if ($type == 'update')
		{
			$return = $this->matakuliah_model->update($id, $data);
		} 
        else 
            $return = FALSE;

		return $return;
	}

	//--------------------------------------------------------------------



}