#include "SVGElements.hpp"

namespace svg
{
    // These must be defined!
    SVGElement::SVGElement() {}
    SVGElement::~SVGElement() {}
    

    Ellipse::Ellipse(const Color &fill, const Point &center, const Point &radius)
        : fill(fill), center(center), radius(radius)
    {
    }
    Point Ellipse::transform(const std::string &t_name, const Point &origin, const int &mul) const
    {
        if(t_name == "translate"){
            return center.translate(origin);
        }
    
        else if(t_name == "rotate"){
            return center.rotate(origin, mul);
    
        }
        else {
            return center.scale(origin, mul);
        }
    }

    void Ellipse::draw(PNGImage &img) const
    {
        img.draw_ellipse(center,radius,fill);
    }









    PolyLine::PolyLine(const Color &stroke, const std::vector<Point>& points)
        : stroke(stroke), points(points)
    {
    }



    std::vector<Point> PolyLine::transform(const std::string &t_name, const Point &origin, const int &mul) const
    {
   
        if(t_name == "translate"){
            std::vector<Point> new_points;
            for(auto p : points){
                new_points.push_back(p.translate(origin));
                }
            return new_points;
        }

        else if (t_name == "rotate"){
            std::vector<Point> new_points;
            for(auto p : points){
                new_points.push_back(p.rotate(origin, mul));
                
                } 
            return new_points; 
        }

        else{
            std::vector<Point> new_points;
            for(auto p : points){
                new_points.push_back(p.scale(origin, mul));
                }
            return new_points;
        }

    }


    void PolyLine::draw(PNGImage &img) const
    {
        for(size_t i = 0; i< points.size() - 1; i++)
            img.draw_line(points.at(i), points.at(i+1), stroke);
    }







    Polygon::Polygon(const Color &fill, const std::vector<Point>& points)
        : fill(fill), points(points)
    {
    }

    std::vector<Point> Polygon::transform(const std::string &t_name, const Point &origin, const int &mul) const{
    if(t_name == "translate"){
        std::vector<Point> new_points;
        
        
        for(auto p : points){
            new_points.push_back(p.translate(origin));
        }
        return new_points;
    }


        else if(t_name == "rotate"){
        std::vector<Point> new_points;
        for(auto p : points){
            new_points.push_back(p.rotate(origin, mul));
        }
        return new_points;
    }


        else{
            std::vector<Point> new_points;
            for(auto p : points){
                new_points.push_back(p.scale(origin, mul));
            }
            return new_points;
        }
    }


        void Polygon::draw(PNGImage &img) const
    {
        img.draw_polygon(points, fill);
    
    }

    
        void Group::draw(PNGImage &img) const
{
    for (const auto &element : elements)
    {
        element->draw(img);
    }
}
    


    Group::Group(const std::vector<SVGElement *> &elements)
    : elements(elements)
{
}
    void Group::transform(const std::string &t_name, const Point &origin, const int &mul) const
{
    for (const auto &element : elements)
    {
        if (Ellipse *ellipse = dynamic_cast<Ellipse*>(element))
        {
            ellipse->transform(t_name, origin, mul);
        }
        else if (PolyLine *polyline = dynamic_cast<PolyLine*>(element))
        {
            polyline->transform(t_name, origin, mul);
        }
        else if (Polygon *polygon = dynamic_cast<Polygon*>(element))
        {
            polygon->transform(t_name, origin, mul);
        }
    }
}


}
