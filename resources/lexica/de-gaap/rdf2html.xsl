<?xml version="1.0"?>

<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">

  <xsl:strip-space elements="*" />
  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes"/>
  
<xsl:template match="/rdf:RDF">  
  <p>
  <xsl:choose>
    <xsl:when test="count(*) = 1">
     <xsl:for-each select="*">
       <h2><xsl:value-of select="@rdf:about"/>
         &#160;&#160;
         <a>
           <xsl:attribute name="href">
             <xsl:value-of select="concat(@rdf:about,'.rdf')"/>
           </xsl:attribute>
           <img src="/img/rdf_flyer.png"/>
         </a>
         <a>
           <xsl:attribute name="href">
             <xsl:value-of select="concat(@rdf:about,'.ttl')"/>
           </xsl:attribute>
           <img src="/img/icon_turtle.gif"/>
         </a>
       </h2>
       <h5>Instance of: <a>
           <xsl:attribute name="href">
           <xsl:value-of select="concat(namespace-uri(),local-name())"/>
           </xsl:attribute>
           <xsl:value-of select="name()"/>
           </a>
       </h5>
       <xsl:call-template name="forprop"/>
    </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
     <xsl:for-each select="*">
       <h2><xsl:value-of select="@rdf:about"/></h2>
       <h5>Instance of: <a>
           <xsl:attribute name="href">
           <xsl:value-of select="concat(namespace-uri(),local-name())"/>
           </xsl:attribute>
           <xsl:value-of select="name()"/>
           </a>
       </h5>
       <xsl:call-template name="forprop"/>
    </xsl:for-each>
    </xsl:otherwise>
  </xsl:choose>
  </p>
</xsl:template>

<xsl:template name="forprop">
  <table class="rdf">
     <tr>
       <th>Property</th>
       <th>Value</th>
     </tr>
     <xsl:for-each select="*">
       <tr>
          <td> <a>
         <xsl:attribute name="href">
         <xsl:value-of select="concat(namespace-uri(),local-name())"/>
         </xsl:attribute>
         <xsl:value-of select="name()"/>
         </a>
         </td>
         <td>
            <xsl:choose>
              <xsl:when test="@rdf:resource">
                <a>
                 <xsl:attribute name="href">
                 <xsl:value-of select="@rdf:resource"/>
                 </xsl:attribute>
                 <xsl:value-of select="@rdf:resource"/>
                </a>
              </xsl:when>
              <xsl:when test="node()[last()]/self::text()">
                 <xsl:if test="@xml:lang">
                 <img>
                   <xsl:attribute name="src">
                     <xsl:value-of select="concat('/img/flags/',@xml:lang,'.gif')"/>
                   </xsl:attribute>
                 </img>
                 </xsl:if>
                 &#x201c;<xsl:value-of select="node()"/>&#x201d;
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each select="*">
                  <xsl:call-template name="forprop2"/>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
         </td>
       </tr>
    </xsl:for-each>
  </table>
</xsl:template>


<xsl:template name="forprop2">
  <table class="rdf">
       <xsl:if test="not(rdf:Description)">
       <tr>
         <td> <a href="http://www.w3.org/1999/02/22-rdf-syntax-ns#type">rdf:type</a></td>
         <td> <a>
         <xsl:attribute name="href">
         <xsl:value-of select="concat(namespace-uri(),local-name())"/>
         </xsl:attribute>
         <xsl:value-of select="name()"/>
         </a></td>
       </tr>
       </xsl:if>
     <xsl:for-each select="*">
       <tr>
          <td> <a>
         <xsl:attribute name="href">
         <xsl:value-of select="concat(namespace-uri(),local-name())"/>
         </xsl:attribute>
         <xsl:value-of select="name()"/>
         </a>
         </td>
         <td>
            <xsl:choose>
              <xsl:when test="@rdf:resource">
                <a>
                 <xsl:attribute name="href">
                 <xsl:value-of select="@rdf:resource"/>
                 </xsl:attribute>
                 <xsl:value-of select="@rdf:resource"/>
                </a>
              </xsl:when>
              <xsl:when test="node()[last()]/self::text()">
                 <xsl:if test="@xml:lang">
                 <img>
                   <xsl:attribute name="src">
                     <xsl:value-of select="concat('/img/flags/',@xml:lang,'.gif')"/>
                   </xsl:attribute>
                 </img>
                 </xsl:if>
                 &#x201c;<xsl:value-of select="node()"/>&#x201d;
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each select="*">
                  <xsl:call-template name="forprop2"/>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
         </td>
       </tr>
    </xsl:for-each>
  </table>
</xsl:template>

</xsl:stylesheet>