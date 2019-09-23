<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
//controller to implement image resize functionality
class Home extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }
	//default 
	function index(){
		$directory = "./uploads";
		$images = glob($directory . "/*.jpg");
		$this->load->view('home',array('images'=>$images));
	}
	//function to submit image and width hegith to manupulate image size
	function submitForm(){
		$rules = array(
            array(
                'field' => 'width',
                'label' => 'Width',
                'rules' => 'required',
                'errors' => array(
                    'required' => 'You must provide a %s.',
                ),
            ),
            array(
                'field' => 'height',
                'label' => 'Height',
                'rules' => 'required',
                'errors' => array(
                    'required' => 'You must provide a %s.',
                ),
            )
        );

        $this->form_validation->set_rules($rules);
		$directory = "./uploads";
		$images = glob($directory . "/*.jpg");
        if ($this->form_validation->run() == FALSE) {
				$width=$this->input->post('width');
				$height=$this->input->post('height');
				$filename = md5(uniqid(rand(), true));
				$config = array(
					'upload_path' => 'uploads',
					'allowed_types' => "gif|jpg|png|jpeg",
					'file_name' => $filename
				);
				$this->load->library('upload', $config);
				if($this->upload->do_upload()){
					$file_data = $this->upload->data();
					$this->resizeImage('./uploads/'.$filename.'.jpg', './resized/'.$filename.'.jpg', $width, $height);
					$data['message'] = "Image uploaded";
				}
		}
		$this->load->view('home',array('images'=>$images));
		
	}

	/**
	 * Resize image - preserve ratio of width and height.
	 * @param string $sourceImage path to source JPEG image
	 * @param string $targetImage path to final JPEG image file
	 * @param int $maxWidth maximum width of final image (value 0 - width is optional)
	 * @param int $maxHeight maximum height of final image (value 0 - height is optional)
	 * @param int $quality quality of final image (0-100)
	 * @return bool
	 */
	function resizeImage($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 80)
	{
		// Obtain image from given source file.
		if (!$image = @imagecreatefromjpeg($sourceImage))
		{
			return false;
		}

		// Get dimensions of source image.
		list($origWidth, $origHeight) = getimagesize($sourceImage);

		if ($maxWidth == 0)
		{
			$maxWidth  = $origWidth;
		}

		if ($maxHeight == 0)
		{
			$maxHeight = $origHeight;
		}

		// Calculate ratio of desired maximum sizes and original sizes.
		$widthRatio = $maxWidth / $origWidth;
		$heightRatio = $maxHeight / $origHeight;

		// Ratio used for calculating new image dimensions.
		$ratio = min($widthRatio, $heightRatio);

		// Calculate new image dimensions.
		$newWidth  = (int)$origWidth  * $ratio;
		$newHeight = (int)$origHeight * $ratio;

		// Create final image with new dimensions.
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
		imagejpeg($newImage, $targetImage, $quality);

		// Free up the memory.
		imagedestroy($image);
		imagedestroy($newImage);

		return true;
	}

}
?>