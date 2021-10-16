<?php 

namespace CRN\Controllers\Logic;

class AttachmentAction {

    const path = "wp-content/uploads/crn-attachments";

    public function upload($file){
        $directory = $this->generate_directory_path();
        $attachment_slug = trailingslashit( $directory ) . basename($file['name']);
        $attachment_uri = trailingslashit( ABSPATH ) . $attachment_slug;
        $result = move_uploaded_file($file['tmp_name'],$attachment_uri);
        return ($result)? $attachment_slug : $result;
    }

    public function generate_directory_path(){
        $year = date("Y");
        $mount = date("m");
        $base_path = trailingslashit( self::path );
        $final_path = $base_path . "{$year}/{$mount}";
        if(!file_exists($final_path)){
            mkdir($final_path,0755,true);
        }
        return $final_path;
    }

}