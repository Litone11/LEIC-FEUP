//! @file shape.hpp
#ifndef __svg_SVGElements_hpp__
#define __svg_SVGElements_hpp__

#include "Color.hpp"
#include "Point.hpp"
#include "PNGImage.hpp"

namespace svg
{
    class SVGElement
    {

    public:
        SVGElement();
        virtual ~SVGElement();
        virtual void draw(PNGImage &img) const = 0;
    };

    // Declaration of namespace functions
    // readSVG -> implement it in readSVG.cpp
    // convert -> already given (DO NOT CHANGE) in convert.cpp

    void readSVG(const std::string &svg_file,
                 Point &dimensions,
                 std::vector<SVGElement *> &svg_elements);
    void convert(const std::string &svg_file,
                 const std::string &png_file);
    // class Ellipse
    class Ellipse : public SVGElement
    {
    public:
    // Ellipse constructor
        Ellipse(const Color &fill, const Point &center, const Point &radius);
    // Draw funct
        void draw(PNGImage &img) const override;
        Point transform(const std::string &t_name, const Point &origin, const int &mul = 0) const;
        void setCenter(const Point &center) { this->center = center; }
        Point getCenter() { return this-> center; }
    private:
        // Ellipse's color
        Color fill;
        // Ellipse's center
        Point center;
        // Ellipse's radius trough the x-axis and the y-axis
        Point radius;
    };

    // class PolyLine
    class PolyLine : public SVGElement
    {
    public:
        // PolyLine constructor
        PolyLine(const Color &stroke, const std::vector<Point>& points);
        // Draw funct
        void draw(PNGImage &img) const override;
        std::vector<Point> transform(const std::string &t_name, const Point &origin,const int &mul = 0) const;
        void setPoints(const std::vector<Point>& points) { this->points = points; }
    private:
        // PolyLine's color
        Color stroke;
        //vector of points (a comma-separated sequence of XY coordinates)
        std::vector<Point> points;
        
    };


    // class Polygon
    class Polygon : public SVGElement
    {
    public:
        // Polygon constructor
        Polygon(const Color &fill, const std::vector<Point>& points);
        // Draw funct
        void draw(PNGImage &img) const override;
        std::vector<Point>  transform(const std::string &t_name, const Point &origin, const int &mul = 0) const;
        void setPoints(const std::vector<Point>& points) { this->points = points; }
    private:
        // Polygon's color
        Color fill;
        //vector of points (a comma-separated sequence of XY coordinates)
        std::vector<Point> points;
        
    };

    //class Group
    class Group : public SVGElement
    {   
        public:
        // Group constructor
        Group(const std::vector<SVGElement *>& elements);
        // Draw funct
        void draw(PNGImage &img) const override;
        void transform(const std::string &t_name, const Point &origin, const int &mul = 0) const;
        private:
        //Elements that belong to the group are in this vector
        std::vector<SVGElement *> elements;
    };


};
#endif