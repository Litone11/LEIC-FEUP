#ifndef __prog_Color_hpp__
#define __prog_Color_hpp__

#include <iostream>

namespace prog {
    /**
    * @typedef rgb_value
    * @brief Represents an RGB color component (0–255).
    */
    typedef unsigned char rgb_value;

    /**
    * @class Color
    * @brief Represents an RGB color with red, green, and blue components.
    *
    * Provides getters and setters for each component, as well as comparison and I/O operations.
    */
    class Color {
    private:
        rgb_value r_;    /**< Red component (0–255). */
        rgb_value g_;    /**< Green component (0–255). */
        rgb_value b_;    /**< Blue component (0–255). */
    public:
        /**
         * @brief Default constructor. Initializes color to black (0, 0, 0).
         */
        Color();

        /**
         * @brief Copy constructor. Initializes color with the values of another Color object.
         * @param c The Color object to copy.
         */
        Color(const Color &c);

        /**
         * @brief Constructor. Initializes color with specified RGB values.
         * @param r Red component (0–255).
         * @param g Green component (0–255).
         * @param b Blue component (0–255).
         */
        Color(rgb_value r, rgb_value g, rgb_value b);

        /**
        * @brief Gets the red component.
        * @return The red component value.
        */
        rgb_value red() const;

        /**
         * @brief Sets the red component.
         * @return A reference to the red component value.
         */
        rgb_value &red();

        /**
         * @brief Gets the green component.
         * @return The green component value.
         */
        rgb_value green() const;

        /**
         * @brief Sets the green component.
         * @return A reference to the green component value.
         */
        rgb_value &green();

        /**
         * @brief Gets the blue component.
         * @return The blue component value.
         */
        rgb_value blue() const;

        /**
         * @brief Sets the blue component.
         * @return A reference to the blue component value.
         */
        rgb_value &blue();

        /**
         * @brief Compares this color with another color for equality.
         * @param other The other Color object to compare with.
         * @return True if the colors are equal, false otherwise.
         */
        bool operator==(const Color& other) const;

    };
}

/**
 * @brief Overloads the input stream operator (>>) for Color.
 * @param input The input stream.
 * @param c The Color object to read into.
 * @return The input stream.
 */
std::istream &operator>>(std::istream &input, prog::Color &c);

/**
 * @brief Overloads the output stream operator (<<) for Color.
 * @param output The output stream.
 * @param c The Color object to write to the stream.
 * @return The output stream.
 */
std::ostream &operator<<(std::ostream &output, const prog::Color &c);


#endif
