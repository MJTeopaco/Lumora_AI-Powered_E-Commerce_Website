<?php
// app/Core/View.php

namespace App\Core;

class View {
    protected $view;
    protected $data = [];
    protected $layout = 'default'; // Default layout file name

    /**
     * View constructor.
     * @param string $view - Path to view (e.g., 'main_page/index')
     * @param array $data - Data to pass to the view
     */
    public function __construct($view, $data = []) {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Sets the layout file to use.
     * @param string $layout - Layout name (e.g., 'auth', 'admin', etc.)
     * @return self
     */
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Static method to create and render a view (fluent interface).
     * @param string $view - Path to view
     * @param array $data - Data to pass
     */
    public static function make($view, $data = []) {
        return new static($view, $data);
    }

    /**
     * Renders the view.
     */
    public function render() {
        // Extract data array to variables for use in the view files
        extract($this->data);

        // Capture the view content first
        $content = $this->getRenderedViewContent();

        // Include the main layout file
        $layoutFile = __DIR__ . '/../Views/layouts/.containers/' . $this->layout . '.layout.php';
        
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            die("Layout not found: $layoutFile");
        }
    }

    /**
     * Captures the output of the actual view file.
     * @return string
     */
    protected function getRenderedViewContent() {
        $viewFile = __DIR__ . '/../Views/layouts/' . $this->view . '.view.php';
        
        if (!file_exists($viewFile)) {
            die("View not found: $viewFile");
        }
        
        // Start output buffering
        ob_start();
        
        // Extract data for the view file
        extract($this->data);
        
        // Include the view file
        require $viewFile;
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    // Allows object to be treated as a string (e.g., echo View::make(...))
    public function __toString() {
        // Render and return the output
        $this->render(); 
        return '';
    }
}