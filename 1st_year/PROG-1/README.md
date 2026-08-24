
# Programming project

## Group elements

Identify all group elements (numbers and names).

- up202307344 Henrique Gonçalves
- up202304025 Luis Martins
- up202304904 Santiago Ferreira


## Accomplished tasks

SVG-to-PNG Conversion Project

In the project, we've implemented SVG reading logic to extract image dimensions and SVG elements. We've defined C++ classes for SVG geometric elements like <ellipse>, <circle>, etc., ensuring they can be rendered into PNG format.

Implementing transformations specified in SVG elements, like translate, rotate, and scale, was a significant aspect of the project. Getting these transformations right was key to ensuring that the rendered PNG images looked just like their SVG counterparts.

We took care of groups (<g>) and duplication (<use>) to ensure everything looks just as it should in SVG. So, when there are groups, everything inside sticks together, and when you duplicate elements, they show up in the right places, just like in the original SVG. All set for accurate rendering and copying!

We've provided suggestions for efficient coordinate transformations and memory management throughout the project.

To use the program, compile it using a C++ compiler and provide the SVG file as input. The program will generate the corresponding PNG image.

