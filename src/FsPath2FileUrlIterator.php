<?php

namespace alcamo\uri;

/**
 * @brief Convert filesystem paths to file: URLs
 *
 * @warning If the constructor is called with an instance of FileUriFactory()
 * that does not apply realpath(), this class does not check whether the
 * elements supplied by the inner iterator are legal paths.
 *
 * @date Last reviewed 2025-10-08
 */
class FsPath2FileUrlIterator extends \IteratorIterator
{
    private $fileUriFactory_; ///< FileUriFactory

    public function __construct(
        \Iterator $iterator,
        ?FileUriFactory $fileUriFactory = null
    ) {
        parent::__construct($iterator);

        $this->fileUriFactory_ = $fileUriFactory ?? new FileUriFactory();
    }

    public function current()
    {
        return $this->fileUriFactory_->create(parent::current());
    }
}
