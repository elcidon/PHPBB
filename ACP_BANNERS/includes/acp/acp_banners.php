<?php

class acp_banners
{
	/**
	 * Default delimiter
	 */
	const DELIMITER = ';';
	
	/**
	 * No image name
	 */
	 var $noimage = 'noimage.jpg';
	
	/**
	 * The URL of the MODEL
	 */
	var $u_action;
	/**
	 * Here is the allowed extensions for uploaded images
	 */
	var $allowed_extensions = array('jpg', 'png');
	
	/**
	 * The main method
	 */
	function main($id, $mode)
    {
    	
		/**
		 * Setting the default config for the module 
		 */
		global $db, $user, $auth, $template;
        global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx; 
		
		$this->page_title = 'Cadastro de Banners';
	    $this->tpl_name = 'acp_banners';
		
		$template->assign_vars(array(
			'PHPBB_ROOT_PATH' 		=> $phpbb_root_path,
			'B_ACP_BANNERS_TITLE' 	=> 'Cadastro de banners',
			'B_ACP_BANNERS_EXPLAIN' => 'Seção para cadastro de banners que serão exibidos em cada tópico (dependendo de estado e categoria selecionada).',
			'U_ACTION'				=> $this->u_action,
		));
		
		$action = request_var('action', '');
	
		/**
		 * The operating actions of the model
		 */	
		if( isset($_POST['add'] )):
			$action = 'add';
		elseif( isset($_POST['save'] )):
			$action = 'save';
		else:
			$action = $action;
		endif;
		
		switch ( $action ):
	
			case 'add':
				
				/**
				 * return the region's slug and name to the system
				 */
				$sql = 'SELECT DISTINCT forum_id, forum_name
		        FROM '. FORUMS_TABLE .' WHERE parent_id = 0';
		
				$result = $db->sql_query($sql);
				$array_region = array();
				while ( $row = $db->sql_fetchrow($result) ):
					
					// //if 'estado' is empty, don't show in the dropdown
					// if( $row['estado'] == "" ):
						// continue;	
					// endif;
					
					//Set the element option with the values
					$template->assign_block_vars( 'loop_categories', array(
						'CATEGORIES' => '<option value="'.$row['forum_id'].'">'.$row['forum_name'].'</option>',
					));
				endwhile;				
				unset($sql);

				
				// Set the fields like the banner label, banner position(input), etc
				for ( $i = 1; $i <= 6; $i++ ):

					$template->assign_block_vars('add_banners',array(	
						'BANNER_LABEL'	  => 'Banner '.$i,					
						'BANNER_POSITION' => '<input type="text" name="position_banner'.$i.'" value="'.$i.'"/>',
						'BANNER_NAME' 	  => '<input type="file" name="banner'.$i.'" value=""/>',
						'BANNER_URL'	  => '<input type="text" name="url_banner'.$i.'" value="http://www.garotasvipacompanhantes.com.br/Acompanhantes/AnunciarModelos"/>',
					));
				endfor;				
								
				$template->assign_vars( array(
					'ADD_BANNERS' => true,
				) );				

				break;
			
			// If clicked on the button 'save'
			case 'save':
				
				// Return the message(There is two action to the same method, INSERT or UPDATE)
				$message = $this->save();
				trigger_error($message.adm_back_link($this->u_action));
				break;
			
			case 'edit':
				
				// Variable passed by GET method
				$banner_id = request_var('id', 0);
				
				/**
				 * Bring the banners registered
				 */
				$sql = "
					SELECT *
					FROM acp_banners
					WHERE id = {$banner_id}
				";

				$result = $db->sql_query($sql);
				$line = $db->sql_fetchrow($result);

				$i = 1;
				foreach ( $line as $key => $value ):
					/**
					 * If fouded one of these below, ignore it
					 */
					if($key == 'id' || $key == 'category_id' || $key == 'forum_id')
					{
						continue;
					}
					
					/**
					 * Ok, The system saves the banner's name with this syntax:
					 * position;banner_name;url
					 * So we can explode this string on delimiter ';' (default) to bring the real name 
					 */
					$exploded 	= explode(acp_banners::DELIMITER, $value);
					$position 	= $exploded[0];
					$bannerName = $exploded[1];
					$bannerUrl 	= $exploded[2];
					
					/**
					 * Set the assign block vars 
					 */
					$template->assign_block_vars('edit_banners',array(
						'BANNER_LABEL'	  => 'Banner '.$i,
						'BANNER_IMAGE'	  => '<img src="'.$phpbb_root_path.'images/banners/'.$bannerName.'" style="width: 250px; height: 100px;" />',
						'BANNER_POSITION' => '<input type="text" name="position_banner'.$i.'" value="'.$position.'"/>',
						'BANNER_NAME' 	  => '<input type="file" name="banner'.$i.'" value="'.$bannerName.'"/>',
						'BANNER_HIDDEN'	  => '<input type="hidden" name="banner'.$i.'" value="'.$bannerName.'"/>',
						'BANNER_URL'	  => '<input type="text" name="url_banner'.$i.'" value="'.$bannerUrl.'"/>',
					));					
					
					$i++;
				endforeach;
				
				/**
				 * Bring the region and category saved in database
				 */
				$category = $line['category_id'];
				$estado   = $line['forum_id'];

				

				$template->assign_vars(array(
					'BANNERS_PATH' => $phpbb_root_path.'images/banners/',
					'EDIT_BANNERS' => true,
					'BANNER_ID' => $line['id'],
					'CATEGORIA' => $categoria,
					'ESTADO' 	=> $estado,					
				));
				unset($sql);
				
				
				$sql = "
					SELECT
						forum_id,
						forum_name
					FROM
						phpbb_forums
					WHERE
						parent_id = $category;	
				";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					/**
					 * if the slug is equal to the category of banner, make it been selected
					 */
					if( $row['forum_id'] == $categoria ):
						$template->assign_block_vars('loop_regions', array(
							'REGIONS'  => '<option value="'.$row['forum_id'].'" selected>'.$row['forum_name'].'</option>',
						));
					else:
						$template->assign_block_vars('loop_regions', array(
							'REGIONS'  => '<option value="'.$row['forum_id'].'">'.$row['forum_name'].'</option>',
						));
					endif;
				}
				
				
				/**
				 * Bring Regions
				 */
				$sql = 'SELECT DISTINCT forum_id, forum_name
		        FROM '. FORUMS_TABLE .' WHERE parent_id = 0';
		
				$result = $db->sql_query($sql);
				
				$array_region = array();
				while ( $row = $db->sql_fetchrow($result) ):
					if( $row['forum_name'] == "" ):
						continue;	
					endif;
					/**
					 * if the region bringed is equal to the region of banner, make it been selected
					 */
					if( $estado == $row['forum_id'] ):
						$template->assign_block_vars( 'loop_categories', array(
							'CATEGORIES' => '<option value="'.$row['forum_id'].'" selected>'.$row['forum_name'].'</option>',
						));
					else:
						$template->assign_block_vars( 'loop_categories', array(
							'CATEGORIES' => '<option value="'.$row['forum_id'].'">'.$row['forum_name'].'</option>',
						));
					endif;	
				endwhile;
				unset($sql);			
				
				break;
			case 'delete':
				
				$banner_id = request_var('id', 0);
				
				if( !$banner_id )
				{
					trigger_error('ID não existe!'.adm_back_link($this->u_action), E_USER_WARNING);
				}
				
				if( confirm_box(true) )
				{				
					$sql = "
						DELETE FROM
							acp_banners
						WHERE
							id = $banner_id
					";
					
					$db->sql_query($sql);
					trigger_error("Banner deletado".adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, 'Tem certeza que deseja excluir esse registro?', build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'id'		=> $banner_id,
						'action'	=> 'delete',
					)));
				}
				
				break;
			
		endswitch;
			
		/**
		 * Bring the banners saved on database
		 */	
		$sqlEstado = 'SELECT b.id, f.forum_name as f1_name, f2.forum_name as f2_name
					    FROM acp_banners b
					    LEFT JOIN phpbb_forums f ON ( f.forum_id = b.category_id )
					    LEFT JOIN phpbb_forums f2 ON ( f2.forum_id = b.forum_id )
					    ORDER BY category_id';
		$result = $db->sql_query($sqlEstado);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('banners', array(
				'ESTADO'		=> $row['f1_name'],
				'CATEGORIA'		=> $row['f2_name'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row['id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row['id']
			));
		}
		
		$db->sql_freeresult($result);
	        
    }

	/*
	 * Check the files sended by the form
	 * @param array $_FILES
	 */
	function save()
	{		
		
		global $template, $db;
		
		$banner_id = $_POST['banner_id'];
		$banners   = array();
		$i 		   = 1;
		$estado    = request_var('forum_id', '');
		$categoria = request_var('category_id', '');
		
		if ( "" === $estado || "" === $categoria ):
			trigger_error('Categoria ou Estado Inválido!' . adm_back_link($this->u_action), E_USER_WARNING);
		endif;
		
			$i=1;
			foreach ( $_FILES as $key => $value ):
				
				/**
				 * If the name of file is empty AND is setted $_POST['banner_id']
				 */
				if( '' == $value['name'] && $_POST['banner_id'] )
				{
					//set the values with the $_POST variable, there is a hidden fields with the name of images
					$banners[$key] = $_POST[$key];
				}
				/**
				 * Else if the Only the upload image is empty, mean that there is no hidden fields with the image
				 */
				elseif( '' == $value['name'] )
				{
					$banners[$key] = $this->noimage;
				}
				else
				{
					
					$dot_pos  = stripos($value['name'], '.');
					$file_ext = substr($value['name'], $dot_pos+1);
				
					if( !in_array( $file_ext, $this->allowed_extensions ) ):
						$message = 'Extensão Inválida!';
						trigger_error($message.adm_back_link($this->u_action), E_USER_WARNING);				
					else:
					
						$tempname = $value['tmp_name'];
						//$filename = $categoria.'_'.$estado.'-'.$i.'.'.$file_ext;
						$filename = str_replace(' ', '_', $value['name']);
						move_uploaded_file($tempname, '../images/banners/'.$filename);
						
						$banners[$key] = $filename;
						
						$i++;
						
					endif;	
				}
				
			endforeach;			
		
		$sql_array = array();
		$sql_array['forum_id']	  = $estado;
		$sql_array['category_id'] = $categoria;
	
		/**
		 * Create the string to save in database
		 * Syntax: position;banner_name;url
		 */
		foreach ( $banners as $key => $value ):
			$sql_array[$key] = $_POST['position_'.$key].acp_banners::DELIMITER.$value.acp_banners::DELIMITER.$_POST['url_'.$key];
		endforeach;
		
		/**
		 * If we have the banner ID, so upload it!
		 */
		if( isset($banner_id) )
		{
			$sql = "UPDATE acp_banners SET " . $db->sql_build_array('UPDATE', $sql_array) . ' WHERE id = ' .$banner_id;
			$message = 'Banners alterados com sucesso!';  
		}
		else
		{
			$sql = "INSERT INTO acp_banners " . $db->sql_build_array('INSERT', $sql_array);
			$message = 'Banners inseridos com sucesso!';
		}

		if ( $db->sql_query( $sql ) ):
			$template->assign_vars( array(
				'B_MESSAGE'  => $message,
				'B_ALERTBOX' => 'success successbox',
			));
			return $message;
		endif;
		
	}

}
