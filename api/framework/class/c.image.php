<?

/**
 * Image Class
 * GD Library required
 * http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
 */
class Image {

    private $mImage;
    private $mWidth;
    private $mHeight;
    private $mImageResized;

    const EXTRAXT   = 'EXTRACT';
    const PORTRAIT  = 'PORTRAIT';
    const LANDSCAPE = 'LANDSCAPE';
    const AUTO      = 'AUTO';
    const CROP      = 'CROP';

    /**
     * 
     *
     * @param string $pImageBase64
     */
    function __construct(string $pImageBase64) {
        // *** Open up the file  
        $this->mImage = imagecreatefromstring(base64_decode($pImageBase64));

        // *** Get width and height  
        $this->mWidth  = imagesx($this->mImage);
        $this->mHeight = imagesy($this->mImage);
    }

    /**
     * Resize an image
     *
     * @param integer $pToWidth
     * @param integer $pToHeight
     * @param integer $pOption
     * @return string
     */
    public function Resize(int $pToWidth, int $pToHeight, int $pOption = self::AUTO): string {
        // Get optimal width and height - based on $option  
        $vToDimension = $this->FixDimensions($pToWidth, $pToHeight, $pOption);

        // Resample - create image canvas of x, y size  
        $this->mImageResized = imagecreatetruecolor($vToDimension[0], $vToDimension[1]);
        imagecopyresampled($this->mImageResized, $this->mImage, 0, 0, 0, 0, $vToDimension[0], $vToDimension[1], $this->mWidth, $this->mHeight);

        // Phisically Crop  
        if ($pOption == self::CROP) {
            $this->Crop($vToDimension[0], $vToDimension[1], $pToWidth, $pToHeight);
        }

        // Get type
        $vType = exif_imagetype($this->mImageResized);

        // Base64 Image Resized
        $vBase64ImageResized = 'data:image/' . $vType . ';base64,' . base64_encode($this->mImageResized);

        // Destroy GdImage
        imagedestroy($this->mImageResized);

        return $vBase64ImageResized;
    }

    /**
     * Fix the dimension of an image
     *
     * @param integer $pToWidth
     * @param integer $pToHeight
     * @param integer $pOption
     * @return array
     */
    private function FixDimensions(int $pToWidth, int $pToHeight, int $pOption): array {
        switch ($pOption) {
            case self::EXTRAXT:
                return array($pToWidth, $pToHeight);
                break;
            case self::PORTRAIT:
                return array($this->GetWidthByFixedHeight($pToWidth), $pToHeight);
                break;
            case self::LANDSCAPE:
                return array($pToWidth, $this->GetHeightByFixedWidth($pToWidth));
                break;
            case self::CROP:
                return $this->GetCropSize($pToWidth, $pToHeight);
                break;
            case self::AUTO:
            default:
                return $this->GetAutoSize($pToWidth, $pToHeight);
                break;
        }
    }

    /**
     * Get the width by a given height
     *
     * @param integer $pToHeight
     * @return integer
     */
    private function GetWidthByFixedHeight(int $pToHeight): int {
        $vRatio = $this->mWidth / $this->mHeight;
        return $pToHeight * $vRatio;
    }

    /**
     * Get the height by a given width
     *
     * @param integer $pToWidth
     * @return integer
     */
    private function GetHeightByFixedWidth(int $pToWidth): int {
        $vRatio = $this->mHeight / $this->mWidth;
        return $pToWidth * $vRatio;
    }

    /**
     * Calculate the size automatically
     *
     * @param integer $pToWidth
     * @param integer $pToHeight
     * @return array
     */
    private function GetAutoSize(int $pToWidth, int $pToHeight): array {
        // Image to be resized is wider (landscape) 
        if ($this->mHeight < $this->mWidth)
            return array($pToWidth, $this->GetHeightByFixedWidth($pToWidth));
        // Image to be resized is taller (portrait)  
        elseif ($this->mHeight > $this->mWidth)
            return array($this->GetWidthByFixedHeight($pToHeight), $pToHeight);
        // Image to be resized is a square  
        else {
            if ($pToHeight < $pToWidth) {
                return array($pToWidth, $this->GetHeightByFixedWidth($pToWidth));
            } else if ($pToHeight > $pToWidth) {
                return array($this->GetWidthByFixedHeight($pToHeight), $pToHeight);
            } else {
                // Squaure image being resized to a square  
                return array($pToWidth, $pToHeight);
            }
        }
    }

    /**
     * Calculate the Crop size
     *
     * @param integer $pToWidth
     * @param integer $pToHeight
     * @return array
     */
    private function GetCropSize(int $pToWidth, int $pToHeight): array {
        $vHeightRatio = $this->mHeight / $pToHeight;
        $vWidthRatio  = $this->mWidth /  $pToWidth;
        $vOptiomalRatio = $vHeightRatio < $vWidthRatio ? $vHeightRatio : $vWidthRatio;

        return array($this->mWidth  / $vOptiomalRatio, $this->mHeight / $vOptiomalRatio);
    }

    /**
     * Phisically crop an image
     *
     * @param integer $pOtimalWidth
     * @param integer $pOptimalHeight
     * @param integer $pToWidth
     * @param integer $pToHeight
     * @return void
     */
    private function Crop(int $pOtimalWidth, int $pOptimalHeight, int $pToWidth, int $pToHeight) {
        // Find center - this will be used for the crop  
        $vCropStartX = ($pOtimalWidth / 2) - ($pToWidth / 2);
        $vCropStartY = ($pOptimalHeight / 2) - ($pToHeight / 2);

        // Clone image
        $cropImage = $this->mImageResized;

        // Now crop from center to exact requested size  
        $this->mImageResized = imagecreatetruecolor($pToWidth, $pToHeight);
        imagecopyresampled($this->mImageResized, $cropImage, 0, 0, $vCropStartX, $vCropStartY, $pToWidth, $pToHeight, $pToWidth, $pToHeight);
    }
}
