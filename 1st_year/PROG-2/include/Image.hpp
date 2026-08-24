#ifndef __prog_Image_hpp__
#define __prog_Image_hpp__

#include "Color.hpp"
#include <vector>

namespace prog {
    /* @class Image
     * @brief Class representing an image with a width, height, and pixels.
     */
    class Image {
    private:
        int width_;   /**< Width of the image in pixels. */
        int height_;  /**< Height of the image in pixels. */
        std::vector<std::vector<Color>> pixels_; /**< 2D vector of Color objects representing the pixels of the image. */
	public:
        /* @brief Constructor for the Image class.
         * @param w Width of the image in pixels.
         * @param h Height of the image in pixels.
         * @param fill Color to fill the image with (default is white).
         */
        Image(int w, int h, const Color &fill = {255, 255, 255});

        /**
        * @brief Destructor for the image.
        */
        ~Image();

        /**
        * @brief Gets the width of the image.
        * @return The image width.
        */
        int width() const;

        /**
         * @brief Gets the height of the image.
         * @return The image height.
         */
        int height() const;


        /**
        * @brief Accesses a pixel at the given position (mutable).
        *
        * @param x X-coordinate (column).
        * @param y Y-coordinate (row).
        * @return Reference to the Color at (x, y).
        */
        Color &at(int x, int y);

        /**
         * @brief Accesses a pixel at the given position (const).
         * @param x X-coordinate (column).
         * @param y Y-coordinate (row).
         * @return Const reference to the Color at (x, y).
         */
        const Color &at(int x, int y) const;
    };
}
#endif
