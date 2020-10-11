<?php
# github.com/Dreller/xploder
class xploder{

    private $prefix = 'temp_wip_';
    private $parts = 8;
    private $file = '';
    private $dir = '';
    private $key = '';
    private $salt = '';

    public function boom($path, $key = ''){
        # Store key
        $this->key = $key;
        
        # If a key is set, we must encrypt the file.
        if( $this->key != '' ){
            $this->lock($path);
        }
        
        # Extract file name
        $this->file = basename($path);
        
        # Extract folder
        $this->dir = dirname($path);
        
        # Go to folder
        chdir($this->dir);
        
        # Split file
        $cmd = 'split "' . $this->file . '" ' . $this->prefix . ' -d -a 4 -n ' . $this->parts;
        exec($cmd);
        
        # Remove exploded file
        unlink($path);                
    }
    
    public function rebuild($folder, $key){
        $this->dir = $folder;
        $this->key = $key;
        
        # Go to the folder
        chdir($this->dir);
        
        $cmd = 'cat ' . $this->prefix . '* > temp';
        exec($cmd);
        
        # If a key is set, we must decrypt the file.
        if( $this->key != '' ){
            $this->unlock();
        }
        
    }

    protected function lock($path){
        $this->salt = crypt($this->key, '7xJamKYuD9X7APTp78R6DmQ4WGTcqjd5kEhj5zUtYvWeWadRPRrQVfNndR3vtb4AeDZweTn55WnG6QCDqpy6FYQvzBjGaYGykRX5');
        $iv = substr( hash('sha3-512', $this->salt), 0, 16 );
        $locked = openssl_encrypt( file_get_contents($path), 'AES-256-CBC', $this->key, 0, $iv);
        
        unlink($path);
        file_put_contents($path, $locked);       
    }
    
    protected function unlock(){
        $this->salt = crypt($this->key, '7xJamKYuD9X7APTp78R6DmQ4WGTcqjd5kEhj5zUtYvWeWadRPRrQVfNndR3vtb4AeDZweTn55WnG6QCDqpy6FYQvzBjGaYGykRX5');
        $iv = substr( hash('sha3-512', $this->salt), 0, 16 );
        $unlocked = openssl_decrypt( file_get_contents('temp'), 'AES-256-CBC', $this->key, 0, $iv);
        
        unlink('temp');
        file_put_contents('temp', $unlocked);       
    }
    
    


    /**
     * Set the number of parts to explode the file.
     */
    public function setParts($partsNumber){
        $this->$parts = $partsNumber;
    }

}