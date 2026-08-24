#include "Image.hpp"
#include <iostream>

namespace prog {
    Image::Image(int w, int h, const Color &fill)
        : width_(w), height_(h), pixels_(h, std::vector<Color>(w, fill)) {}

    Image::~Image() {}

    int Image::width() const {
        return width_;
    }

    int Image::height() const {
        return height_;
    }

    Color &Image::at(int x, int y) {
        if(x < 0 || x >= width_ || y < 0 || y >= height_) {
            std::cout << "Pixel coordinates are out of bounds" << std::endl;
        }
        return pixels_[y][x];
    }

    const Color &Image::at(int x, int y) const {
        if(x < 0 || x >= width_ || y < 0 || y >= height_) {
            std::cout << "Pixel coordinates are out of bounds" << std::endl;
        }
        return pixels_[y][x];
    }
}
