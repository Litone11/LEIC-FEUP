#include <iostream>
#include <sstream>
#include "SVGElements.hpp"
#include "external/tinyxml2/tinyxml2.h"

using namespace std;
using namespace tinyxml2;

namespace svg
{
    void readElements(XMLElement* elem, vector<SVGElement *>& svg_elements){
        vector<Point> points;
        string t_name;
        int mul;
        bool flag1 = false;
        Point origin = {0,0};
    
        if(elem->Attribute("transform") != NULL){
            string transform = elem->Attribute("transform");
            t_name = transform.substr(0, transform.find_first_of("("));
            mul = stoi(transform.substr(transform.find_first_of("(") + 1, transform.find_first_of(")")));
            flag1 = true;
            
        }

        if(elem->Attribute("transform-origin") != NULL){
            string origin_str = elem->Attribute("transform-origin");
            int x = stoi(origin_str.substr(0, origin_str.find_first_of(" ")));
            int y = stoi(origin_str.substr(origin_str.find_first_of(" ")+1));
            origin = {x, y};
            
        }
        if(strcmp(elem->Name(), "circle") == 0){
            Ellipse *circle = new Ellipse(Color(parse_color(elem->Attribute("fill"))),{elem->IntAttribute("cx"), elem->IntAttribute("cy")}, {elem->IntAttribute("r"), elem->IntAttribute("r")});
            
            
            if (flag1){
                circle->setCenter(circle->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(circle);
        }

        else if(strcmp(elem->Name(), "ellipse") == 0){
            Ellipse *ellipse = new Ellipse(Color(parse_color(elem->Attribute("fill"))),{elem->IntAttribute("cx"), elem->IntAttribute("cy")}, {elem->IntAttribute("rx"), elem->IntAttribute("ry")});
            
            if (flag1){
                ellipse->setCenter(ellipse->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(ellipse);
        }

        else if(strcmp(elem->Name(), "line") == 0){
            points.push_back({(elem->IntAttribute("x1"),elem->IntAttribute("y1"))});
            points.push_back({elem->IntAttribute("x2"),elem->IntAttribute("y2")});
            PolyLine *line = new PolyLine(Color(parse_color(elem->Attribute("stroke"))), points);
            
            if (flag1){
                line->setPoints(line->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(line);
            points.clear();
        }

        else if(strcmp(elem->Name(), "polyline") == 0){
            istringstream iss(elem->Attribute("points"));
            for(string l; iss >> l;)
            {
                int x = stoi(l.substr(0,l.find_first_of(",")));
                int y = stoi(l.substr(l.find_first_of(",") + 1));
                points.push_back({x,y});
            }
            PolyLine *polyline = new PolyLine(Color(parse_color(elem->Attribute("stroke"))), points);
            if (flag1){
                polyline->setPoints(polyline->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(polyline);
            points.clear();
        }

        else if(strcmp(elem->Name(), "polygon") == 0){
            istringstream iss(elem->Attribute("points"));
            for(string l; iss >> l;)
            {
                int x = stoi(l.substr(0,l.find_first_of(",")));
                int y = stoi(l.substr(l.find_first_of(",") + 1));
                points.push_back({x,y});
            }
            Polygon *polygon = new Polygon(Color(parse_color(elem->Attribute("fill"))), points);
            if (flag1){
                polygon->setPoints(polygon->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(polygon);
            points.clear();
        }
        else if(strcmp(elem->Name(), "rect") == 0){
            
            struct Point tl{elem->IntAttribute("x"), elem->IntAttribute("y")};
            struct Point bl{tl.x , tl.y + elem->IntAttribute("height")}, tr{tl.x + elem->IntAttribute("width"), tl.y}, br{tl.x + elem->IntAttribute("width"), tl.y + elem->IntAttribute("height")};
        
            points.push_back(tl);
            points.push_back(bl);
            points.push_back(tr);
            points.push_back(br);
        
            Polygon *rect = new Polygon(Color(parse_color(elem->Attribute("fill"))), points);
            if (flag1){
                rect->setPoints(rect->transform(t_name, origin, mul));
            }  
            svg_elements.push_back(rect);
            points.clear();
        }

        else if(strcmp(elem->Name(), "g") == 0){
            for (XMLElement *child = elem->FirstChildElement(); child != nullptr; child = child->NextSiblingElement())
            {   
                
                vector<SVGElement *>svg_elements_g;
                readElements(child, svg_elements_g);
                Group *group = new Group(svg_elements_g);
                svg_elements.push_back(group);
                svg_elements_g.~vector();

                
                
            }
            
        }

    }

    void readSVG(const string& svg_file, Point& dimensions, vector<SVGElement *>& svg_elements)
    {
        svg_elements.clear();
        XMLDocument doc;
        XMLError r = doc.LoadFile(svg_file.c_str());
        if (r != XML_SUCCESS)
        {
            throw runtime_error("Unable to load " + svg_file);
        }
        XMLElement *xml_elem = doc.RootElement();

        dimensions.x = xml_elem->IntAttribute("width");
        dimensions.y = xml_elem->IntAttribute("height");
    
        for (XMLElement *child = xml_elem->FirstChildElement(); child != nullptr; child = child->NextSiblingElement())
    {
        readElements(child, svg_elements);
    }
        

    }


    
}